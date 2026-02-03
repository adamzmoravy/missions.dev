<?php

use Karolina\Notification as Notification;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Ramsey\Uuid\Uuid;
use Communicator\SignedJwt as Message;
use Karolina\Experiment\NewExperimentsNewsletter;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VideoGrant;

$agitator = $app['controllers_factory'];


$agitator->post('/experiments/{experimentId}/share/', function ($experimentId, Request $request) use ($app) {


    $app['platform']->limit('share_experiment', 50, 600);
    

    $experimentId = $experimentId;
    $message = $request->get('message', '');
    $shareWithUserIds = $request->get('profile_ids', []);

	$interactor = $app['experimentInteractor'];
    $interactor->setExperimentById($experimentId);

    $currentUser = $app['currentUser'];

    $interactor->shareExperiment($shareWithUserIds, $message, $currentUser, $app['notificationService'], $app['platform']);

    $response['status'] = "success";
    $response['message'] = "Experiment has been shared.";
    return $app->json($response);

})->before($authRequired);

$agitator->get('/experiments/spaces/', function (Request $request) use ($app) {

    $repo = new \Karolina\Experiment\SpaceRepository();

    $spaces = $repo->getSpaces();

    $spacesResponses = [];
    foreach ($spaces as $space) {
        $spacesResponses[] = (new \Karolina\API\v1\SpaceResponse($space))->get();
    }

    $response['status'] = "success";
    $response['spaces'] = $spacesResponses;
    return $app->json($response);

});

/*

$agitator->get('/experiments/fixsettingsfield/', function (Request $request) use ($app) {

    $models = new Karolina\Database\Table\Experiment();
    $models = $models->all();
    foreach ($models as $model) {
        if ($model->settings == '[]') {
            $model->settings = "{}";
            $model->save();
        }

    }
//    $models->update(['settings' => '{}']);

});

*/

$agitator->get('/experiments/unsubscribe/{token}', function ($token, Request $request) use ($app) {

    // Validate mesage
    $message = new Message($app['platform']->getSecretKey());
    $message->fromTokenString($token);
    $data = $message->readAll();


    $userEditor = $app['userEditor'];

    
    if ($data['unsubscribe_to_topic'] == 'newsletter') {
        $userEditor->setUserById($data['unsubscribe_user_id']);
        $userEditor->updateSettings(array('unsubscribed_to_newsletter' => true));

        return "<center><h1>You have been unsubscribed.</h1></center>";
    
    }
 

});

$agitator->post('/experiments/{experimentId}/photo_stream/', function ($experimentId, Request $request) use ($app) {

    $creator = $app['currentUser'];
    $editor = $app['experimentEditor'];
    $editor->setExperimentById($experimentId);
    $imageRecordRepository = new \Karolina\Image\ImageRecordRepository();

    $editor->addToPhotoStream($request->get('filename'), $imageRecordRepository);

    $response['status'] = "success";
    return $app->json($response);

})->before($authRequired);



$agitator->post('/experiments/{experimentId}/calendar/events/', function ($experimentId, Request $request) use ($app) {

	$interactor = $app['experimentInteractor'];
	$document = $interactor->getDocumentAsUser($experimentId, 'EN', $app['currentUser']);

	$currentUser = $app['currentUser'];

	if (!$currentUser and getenv('access') == 'members_only') {

	    throw new \Karolina\Exception("This submissions is only accessible to logged in users", "access_denied", "200");
    }


    $interactor = new Karolina\Happening\CalendarInteractor();

    $start = $request->get('start');
    $end = $request->get('end');
    $calendars = array(
        'submission-'.$experimentId
    );

    $event = new Karolina\Happening\CalendarEvent(
        $start,
        $end,
        $calendars
    );

    $event->setName($request->get('name'));
    $event->setDescription($request->get('description'));
    $event->setLocation($request->get('location'));
    $event->setUserId($app['currentUser']->getId());
    $event->setMaxParticipants($request->get('max_participants'));
    $event->setPrice($request->get('price'));
    $event->setImage($request->get('image'));

    $interactor->saveEvent($event);

    $response['status'] = "success";
    return $app->json($response);   

    
})->before($authRequired);


$agitator->get('/experiments/{experimentId}/photo_stream/{limit}/', function ($experimentId, $limit, Request $request) use ($app) {

	$interactor = $app['experimentInteractor'];
	$document = $interactor->getDocumentAsUser($experimentId, 'EN', $app['currentUser']);

	$currentUser = $app['currentUser'];

	if (!$currentUser and getenv('access') == 'members_only') {

	    throw new \Karolina\Exception("This submissions is only accessible to logged in users", "access_denied", "200");
    }

    $limit = (int) $limit;

    $imageRecordRepository = new \Karolina\Image\ImageRecordRepository();
    $photoStream = $imageRecordRepository->getImageRecordsByContext('submission-photo-stream-'.$experimentId, $limit);

    $response['photo_stream'] = [];

    foreach ($photoStream as $photo) {

        $photoResponse['id'] = $photo->getId();
        $photoResponse['filename'] = $photo->getFilename();
        $response['photo_stream'][] = $photoResponse;

    }

    $response['status'] = "success";
    return $app->json($response);


});


$agitator->get('/experiments/emailout/{action}/{newer_than}/{min_relevance}/', function ($action, $newer_than, $min_relevance, Request $request) use ($app) {


    $newsletter = new NewExperimentsNewsletter($app, $min_relevance, $newer_than);

    if ($action == 'send') {
    
        $app['platform']->limit('send_newsletter_out', 1, 86400 * 6);

        $newsletter->send();
        return "sent out";

    } else {

        $data = $newsletter->getTemplateData();
        return $app['twig']->render('/notifications/newsletter.twig', $data);

    }



});

$agitator->post('/experiments/from_comment/', function (Request $request) use ($app) {

    $discussionRepository = new \Karolina\Discussion\DiscussionRepository();

    $commentId = (string) $request->request->get('comment_id');
    $comment = $discussionRepository->getComment($commentId, true);
    $creator = $app['userRepository']->getById($comment->getUserId());
    $editor = $app['experimentEditor'];
    $stage = $request->request->get('stage');

    // Must be onwer of comment or admin
	if (!$app['currentUser']) {
        throw new \Karolina\Exception('You need to be logged in to do this', 'access_denied');    
	}

    if (!$app['currentUser']->isAdmin() and $comment->getUserId() != $app['currentUser']->getId()) {
        throw new \Karolina\Exception('You need to be the owner of the comment to do this', 'access_denied');
    }

    $editor->createFromComment($comment, $stage, $creator, $discussionRepository);

    $response['status'] = "success";
    return $app->json($response);

});    

$agitator->post('/experiments/spaces/', function (Request $request) use ($app) {


    // get post variables 
    $name = $request->request->get('name');
    
    $repo = new \Karolina\Experiment\SpaceRepository();
    $repo->createSpace($name);
    
    $response['status'] = "success";
    return $app->json($response);

})->before($adminOnly);


$agitator->delete('/experiments/spaces/{spaceId}/', function ($spaceId, Request $request) use ($app) {

    
    $repo = new \Karolina\Experiment\SpaceRepository();
    $repo->removeSpace($spaceId);
    
    $response['status'] = "success";
    return $app->json($response);

})->before($adminOnly);


$agitator->put('/experiments/spaces/', function (Request $request) use ($app) {

    $repo = new \Karolina\Experiment\SpaceRepository();
    $spaceData = $request->request->get('space');

    if (!$spaceData['space_id']) {
        $space = new Karolina\Experiment\Space();
    } else {
        $space = $repo->getSpaceById($spaceData['space_id']);
    }

    $space->setCustomLanguageFieldsFromArray($space['language']);

    $newSpace = new Karolina\Experiment\Space();
    $newSpace->save();


})->before($adminOnly);


$agitator->get('/experiments/{experimentId}/video_chat/', function ($experimentId, Request $request) use ($app) {

    if ($app['currentUser']) {
        $identity = $app['currentUser']->getId();
    } else {
        throw new \Karolina\Exception('You need to be logged in to access video room', 'access_denied');
    }

    $interactor = $app['experimentInteractor'];

    $experiment = $interactor->setExperimentById($experimentId)->getExperiment();
    $room = getenv('platform_key').',experiment,'.$experimentId.','.$experiment->getUUID();

    // Substitute your Twilio Account SID and API Key details
    $accountSid = getenv('twilio_account');
    $apiKeySid = getenv('twilio_api');
    $apiKeySecret = getenv('twilio_secret');

    // Create an Access Token
    $token = new AccessToken(
        $accountSid,
        $apiKeySid,
        $apiKeySecret,
        3600,
        $identity
    );

    // Grant access to Video
    $grant = new VideoGrant();
    $grant->setRoom($room);
    $token->addGrant($grant);

    $response['status'] = "success";
    $response['identity'] = $identity;
    $response['token'] = $token->toJWT();
    return $app->json($response);


});


$agitator->get('/experiments/admin/{langCode}', function ($langCode, Request $request) use ($app) {


        /** @var \Karolina\Experiment\ExperimentInteractor $interactor */
	$interactor = $app['experimentInteractor'];

	$filterArguments = $request->query->all();

	$experimentsPreviews = $interactor->getPreviewDocuments(
		$langCode,
		$filterArguments
	);


	if ($app['currentUser'] and $app['currentUser']->isAdmin()) {
		$showTeamEmails = true;
	}else{
		$showTeamEmails = false;
	}
	$response['previews'] = array();
	foreach ($experimentsPreviews as $preview) {

		$response['previews'][]
			= (new Karolina\API\v1\ExperimentResponse($preview, $langCode, $app['platform']))->getAdmin($showTeamEmails);

	}

	$response['status'] = "success";

	return $app->json($response);


})->before($adminOnly);

$agitator->get('/experiments/preview/{langCode}', function ($langCode, Request $request) use ($app) {

    if (!$app['currentUser'] and getenv('access') == 'members_only') {
        $response['status'] = "success";
        $response['previews'] = [];
        return $app->json($response);

    }

    /** @var \Karolina\Experiment\ExperimentInteractor $interactor */
	$interactor = $app['experimentInteractor'];

	$filterArguments = $request->query->all();

	$experimentsPreviews = $interactor->getPreviewDocuments(
		$langCode,
		$filterArguments
	);


    $allowedStages = $app['platform']->visibleSubmissionStagesToUser($app['currentUser']);
    $response['allowed_stages'] = $allowedStages;


	if ($app['currentUser'] and $app['currentUser']->isAdmin()) {
		$showTeamEmails = true;
	}else{
		$showTeamEmails = false;
	}
	$response['previews'] = array();
	foreach ($experimentsPreviews as $preview) {



        $responseItem = (new Karolina\API\v1\ExperimentResponse($preview, $langCode, $app['platform']))->getPreview($showTeamEmails);

        // Is user in team?
        $isUserInTeam = false;
        if ($app['currentUser']) {

            if (in_array(strval($app['currentUser']->getId()), $responseItem['team_ids'])) {
                $isUserInTeam = true;
            }
            if ($app['currentUser']->isAdmin()) {
                $isUserInTeam = true;
            }
        }
        // Is stage allowed?
        if ((in_array($responseItem['stage'], $allowedStages) && $responseItem['visibility'] != 'private') || $isUserInTeam) {
            $response['previews'][] = $responseItem;
        }
	}

	$response['status'] = "success";

	return $app->json($response);


});



$agitator->post('/experiments/tags/{langCode}', function ($langCode, Request $request) use ($app) {

    $tagRepository = new \Karolina\Tag\TagRepository();

    $tagLabel = $request->request->get('label');

    $tag = new Karolina\Tag\Tag();
    $tag->setLabel($tagLabel, $langCode);
    $tagRepository->save($tag);


    $response['status'] = "success";
    return $app->json($response);

})->before($adminOnly);


$agitator->put('/experiments/tags/{tagId}/{langCode}', function ($tagId, $langCode, Request $request) use ($app) {

    $tagRepository = new \Karolina\Tag\TagRepository();

    $tagLabel = $request->request->get('label');
    $tag = $tagRepository->getById($tagId);
    $tag->setLabel($tagLabel, $langCode);
    $tagRepository->save($tag);

    $response['status'] = "success";
    return $app->json($response);

})->before($adminOnly);

$agitator->delete('/experiments/tags/{tagId}/', function ($tagId, Request $request) use ($app) {

    $tagRepository = new \Karolina\Tag\TagRepository();

    $tag = $tagRepository->getById($tagId);
    $tag->disable();
    $tagRepository->save($tag);

    $response['status'] = "success";
    return $app->json($response);

})->before($adminOnly);



$agitator->get('/experiments/tags/{langCode}', function ($langCode, Request $request) use ($app) {

	$tagRepository = new \Karolina\Tag\TagRepository();

	$allTags = $tagRepository->getAll('experiment');

	$tagsResponse = array();

	foreach ($allTags as $tag) {

		$tagResponse['id'] = $tag->getId();
		$tagResponse['label'] = $tag->getLabel($langCode);
		$tagsResponse[] = $tagResponse;

	}

	$response['tags'] = $tagsResponse;

	$response['status'] = "success";
	return $app->json($response);

});

$agitator->post('/experiments/{experimentId}/publish/', function ($experimentId, Request $request) use ($app) {

    $editor = $app['experimentEditor'];

	$editor->setExperimentById($experimentId);

    $editor->publishExperiment();

	$response['status'] = "success";
	$response['message'] = "Experiment is published.";
	return $app->json($response);

});

$agitator->post('/experiments/{experimentId}/attachments/file/', function ($experimentId, Request $request) use ($app) {

    $editor = $app['experimentEditor'];
	$editor->setExperimentById($experimentId);
	$file = $request->files->get('file');
    $name = $request->get('name');

    $editor->addFileAttachment($file, $name, $app['s3']);

	$response['status'] = "success";

	return $app->json($response);


})->before($authRequired);

$agitator->delete('/experiments/{experimentId}/attachments/{attachmentId}/', function ($experimentId, $attachmentId, Request $request) use ($app) {

    $editor = $app['experimentEditor'];
	$editor->setExperimentById($experimentId);

    $editor->removeAttachment($attachmentId);

	$response['status'] = "success";

	return $app->json($response);


})->before($authRequired);



$agitator->post('/experiments/{experimentId}/attachments/file_request/', function ($experimentId, Request $request) use ($app) {

	$fileName = $request->get('file_name');

    $attachmentFileRepository = new Karolina\Content\AttachmentFileRepository($app['s3']);
    $signedUrl = $attachmentFileRepository->getSignedUrlToAttachmentFile($fileName, getenv('platform_key').'/submission/'.$experimentId.'/attachments');
    
    $response['signed_url'] = $signedUrl;

	$response['status'] = "success";

	return $app->json($response);


})->before($authRequired);


$agitator->post('/experiments/{experimentId}/team/', function ($experimentId, Request $request) use ($app) {

    /* @var $editor \Karolina\Experiment\ExperimentEditor */
    $editor = $app['experimentEditor'];

	$userRepository = new \Karolina\User\UserRepository();
    $newTeamMemberEmail = $request->request->get('new_team_member');
    $newTeamMemberUser = $userRepository->getByIdentifier($newTeamMemberEmail);

    $editor->setExperimentById($experimentId);
    $editor->addTeamMember($newTeamMemberUser);

    $response['status'] = "success";
    $response['message'] = "New team member has been added.";

    return $app->json($response);

});


$agitator->post('/experiments/{experimentId}/team/me/', function ($experimentId, Request $request) use ($app) {

    /* @var $editor \Karolina\Experiment\ExperimentEditor */
    $editor = $app['experimentEditor'];
    $currentUser = $app['currentUser'];

    $editor->setExperimentById($experimentId);
    $editor->addTeamMember($currentUser);

    $response['status'] = "success";
    $response['message'] = "New team member has been added.";

    return $app->json($response);

});


$agitator->post('/experiments/{experimentId}/team/{userId}/', function ($experimentId, $userId, Request $request) use ($app) {

    /* @var $editor \Karolina\Experiment\ExperimentEditor */
    $editor = $app['experimentEditor'];

	$userRepository = new \Karolina\User\UserRepository();

    $newTeamMemberUser = $userRepository->getById($userId);

    $editor->setExperimentById($experimentId);
    $editor->addTeamMember($newTeamMemberUser);

    $response['status'] = "success";
    $response['message'] = "New team member has been added.";

    return $app->json($response);

});


$agitator->delete('/experiments/{experimentId}/team/{userId}/', function ($experimentId, $userId, Request $request) use ($app) {

    /* @var $editor \Karolina\Experiment\ExperimentEditor */
    $editor = $app['experimentEditor'];

    $userRepository = new \Karolina\User\UserRepository();

    $teamMemberToRemove = $userRepository->getById($userId);

        $editor->setExperimentById($experimentId);
        $editor->removeTeamMember($teamMemberToRemove);

    $response['status'] = "success";
    $response['message'] = "Team member has been removed";

    return $app->json($response);

});


$agitator->delete('/experiments/{experimentId}/publish/', function ($experimentId, Request $request) use ($app) {

    $editor = $app['experimentEditor'];

	$editor->setExperimentById($experimentId);

    $editor->unpublishExperiment();

	$response['status'] = "success";
	$response['message'] = "Experiment is unpublished.";
	return $app->json($response);

});


$agitator->delete('/experiments/{experimentId}/', function ($experimentId, Request $request) use ($app) {

    $editor = $app['experimentEditor'];

	$editor->setExperimentById($experimentId);

    $editor->disableExperiment();

	$response['status'] = "success";
	$response['message'] = "Experiment is disabled.";
	return $app->json($response);

});


$agitator->post('/experiments/{experimentId}/like/', function ($experimentId, Request $request) use ($app) {

    /* @var $interactor \Karolina\Experiment\ExperimentInteractor */
	$interactor = $app['experimentInteractor'];

	if ($currentUser = $app['currentUser']) {

		$interactor->setExperimentById($experimentId);
		$interactor->setCurrentUser($currentUser);
		$interactor->addLike();

		$response['status'] = "success";
		return $app->json($response);

	} else {

		throw new Karolina\KarolinaException('You are not logged in', 401, null, 'not_authorized');
	}


});



$agitator->post('/experiments/{experimentId}/help/', function ($experimentId, Request $request) use ($app) {

    /* @var $interactor \Karolina\Experiment\ExperimentInteractor */
    $interactor = $app['experimentInteractor'];

    if ($currentUser = $app['currentUser']) {

        $interactor->setExperimentById($experimentId);
        $interactor->setCurrentUser($currentUser);
        $interactor->addHelper();

        $response['status'] = "success";
        return $app->json($response);

    } else {

        throw new Karolina\KarolinaException('You are not logged in', 401, null, 'not_authorized');
    }


});


$agitator->delete('/experiments/{experimentId}/help/', function ($experimentId, Request $request) use ($app) {

    $interactor = $app['experimentInteractor'];

    if ($currentUser = $app['currentUser']) {

        $interactor->setExperimentById($experimentId);
        $interactor->setCurrentUser($currentUser);
        $interactor->removeHelper();

        $response['status'] = "success";
        return $app->json($response);

    } else {

        throw new Karolina\KarolinaException('You are not logged in', 401, null, 'not_authorized');
    }


});



$agitator->post('/experiments/{experimentId}/updates/', function ($experimentId, Request $request) use ($app) {

    /* @var $interactor \Karolina\Experiment\ExperimentInteractor */
    $interactor = $app['experimentInteractor'];

    if ($currentUser = $app['currentUser']) {

        $interactor->setExperimentById($experimentId);
        $interactor->setCurrentUser($currentUser);
        $interactor->addUpdatesFollower();

        $response['status'] = "success";
        return $app->json($response);

    } else {

        throw new Karolina\KarolinaException('You are not logged in', 401, null, 'not_authorized');
    }


});


$agitator->delete('/experiments/{experimentId}/updates/', function ($experimentId, Request $request) use ($app) {

    $interactor = $app['experimentInteractor'];

    if ($currentUser = $app['currentUser']) {

        $interactor->setExperimentById($experimentId);
        $interactor->setCurrentUser($currentUser);
        $interactor->removeUpdatesFollower();

        $response['status'] = "success";
        return $app->json($response);

    } else {

        throw new Karolina\KarolinaException('You are not logged in', 401, null, 'not_authorized');
    }


});

$agitator->post('/experiments/{experimentId}/related_experiments/', function ($experimentId, Request $request) use ($app) {


    $interactor = $app['experimentInteractor'];
    $interactor->setExperimentById($experimentId);

    $relatedExperimentIds = $request->request->get('related_experiments');

    foreach ($relatedExperimentIds as $relatedExperimentId) {

        $relatedExperimentInteractor = $app['experimentInteractor'];
        $relatedExperimentInteractor->setExperimentById($relatedExperimentId);
        $relatedExperiment = $relatedExperimentInteractor->getExperiment();

        $interactor->addExperimentRelationship($relatedExperiment);

    }

	$response['status'] = "success";
	return $app->json($response);


})->before($authRequired);


$agitator->delete('/experiments/{experimentId}/related_experiments/{relatedExperimentId}/', function ($experimentId, $relatedExperimentId, Request $request) use ($app) {

    $interactor = $app['experimentInteractor'];
    $interactor->setExperimentById($experimentId);

    $relatedExperimentInteractor = $app['experimentInteractor'];
    $relatedExperimentInteractor->setExperimentById($relatedExperimentId);
    $relatedExperiment = $relatedExperimentInteractor->getExperiment();

    $interactor->removeExperimentRelationship($relatedExperiment);

    $response['status'] = "success";
    return $app->json($response);


})->before($authRequired);


$agitator->delete('/experiments/{experimentId}/like/', function ($experimentId, Request $request) use ($app) {

	$interactor = $app['experimentInteractor'];

	if ($currentUser = $app['currentUser']) {

		$interactor->setExperimentById($experimentId);
		$interactor->setCurrentUser($currentUser);
		$interactor->removeLike();

		$response['status'] = "success";
		return $app->json($response);

	} else {

		throw new Karolina\KarolinaException('You are not logged in', 401, null, 'not_authorized');
	}


});

$agitator->get('/experiments/{experimentId}/relevance/', function ($experimentId, Request $request) use ($app) {

	$interactor = $app['experimentInteractor'];

    $experiment = $interactor->setExperimentById($experimentId)->getExperiment();

    return $experiment->getRelevance();


});


$agitator->get('/experiments/{experimentId}/{langCode}', function ($experimentId, $langCode, Request $request) use ($app) {

	$interactor = $app['experimentInteractor'];
	$document = $interactor->getDocumentAsUser($experimentId, $langCode, $app['currentUser']);
	$currentUser = $app['currentUser'];


    // If platform is for members only, block access to non-logged in users
	if (!$currentUser and getenv('access') == 'members_only') {

	    throw new \Karolina\Exception("This submissions is only accessible to logged in users", "access_denied", "200");
    }


	$experimentResponse = new Karolina\API\v1\ExperimentResponse($document, $langCode, $app['platform']);
    $response['experiment'] = $experimentResponse->get();

    // Check if user can see this submission stage
    $allowedStages = $app['platform']->visibleSubmissionStagesToUser($app['currentUser']);
    if (!in_array($document['stage'], $allowedStages) || $response['experiment']['visibility'] == 'private') {

        if (!$currentUser) {
            throw new \Karolina\Exception("This submission type is not accessible to you", "access_denied", "200");
        }

        // Check if user is team member
        if (in_array(strval($app['currentUser']->getId()), $response['experiment']['team_ids'])) {
            
            // Team member can see their own submissions

        } else if ($app['currentUser']->isAdmin()) {

            // Admin can see all submissions

        } else {
            throw new \Karolina\Exception("This submission type is not accessible to you", "access_denied", "200");

        }

    }



	$response['acl'] = $experimentResponse->getACL($currentUser);
	$response['my_relationships'] = $experimentResponse->getMyRelationships($currentUser);

	$response['status'] = "success";
	return $app->json($response);

});

$agitator->patch('/experiments/{experimentId}/funding/', function ($experimentId, Request $request) use ($app) {

	$editor = $app['experimentEditor'];
	$editor->setExperimentById($experimentId);

	$funding = $request->request->all();
	$editor->updateFunding($funding);

	$response['status'] = "success";
	return $app->json($response);

});


$agitator->post('/experiments/{experimentId}/tags/', function ($experimentId, Request $request) use ($app) {

    $editor = $app['experimentEditor'];
    $editor->setExperimentById($experimentId);

    $tags = $request->request->get('tags');
    $editor->replaceTags($tags);

    $response['status'] = "success";
    $response['message'] = "Tags have been updated.";
    return $app->json($response);

})->before($authRequired);


$agitator->post('/experiments/{experimentId}/tag/', function ($experimentId, Request $request) use ($app) {

    $editor = $app['experimentEditor'];
    $editor->setExperimentById($experimentId);

    $tag = $request->request->get('tag');
    $editor->addTag($tag);

    $response['status'] = "success";
    $response['message'] = "Tags have been updated.";
    return $app->json($response);

})->before($authRequired);

$agitator->delete('/experiments/{experimentId}/tag/{tagId}/', function ($experimentId, $tagId, Request $request) use ($app) {

    $editor = $app['experimentEditor'];
    $editor->setExperimentById($experimentId);

    $editor->removeTag($tagId);

    $response['status'] = "success";
    $response['message'] = "Tags have been updated.";
    return $app->json($response);

})->before($authRequired);

$agitator->post('/experiments/{experimentId}/stagemoves/', function ($experimentId, Request $request) use ($app) {

	$editor = $app['experimentEditor'];
	$editor->setExperimentById($experimentId);

    $direction = $request->request->get('direction');

    switch ($direction) {
        case 'next':
            $editor->moveToNextStage();
        	$response['status'] = "success";
        	$response['message'] = "Stage has been updated.";
            break;
        case 'previous':
            $editor->moveToPreviousStage();
        	$response['status'] = "success";
        	$response['message'] = "Stage has been updated.";
            break;
        default:
            throw new Karolina\KarolinaException('You need to provide direction', 400, null, 'missing_request_parameters');
            break;
    }

	return $app->json($response);

});



$agitator->post('/experiments/{experimentId}/links/', function ($experimentId, Request $request) use ($app) {

	$editor = $app['experimentEditor'];
	$editor->setExperimentById($experimentId);

	$links = $request->request->get('links');
	$editor->replaceLinks($links);

	$response['status'] = "success";
	$response['message'] = "Links have been updated.";
	return $app->json($response);


});

$agitator->get('/experiments/rewritedocuments/', function(Request $request) use ($app) {

	$repo = new Karolina\Experiment\ExperimentRepository($app['ci']);
	$documentsCount = $repo->reWriteAllDocuments();

	$response['count'] = $documentsCount;
	$response['status'] = "success";

	return $app->json($response);

});

$agitator->get('/experiments/resaveallexperiments/', function(Request $request) use ($app) {

    $repo = new Karolina\Experiment\ExperimentRepository($app['ci']);
    $experiments = $repo->getAll();

    foreach ($experiments as $experiment) {
        $repo->save($experiment);
    }


    $response['status'] = "success";

    return $app->json($response);

});


$agitator->post('/experiments/', function (Request $request) use ($app) {

	$interactor = $app['experimentInteractor'];
    
	$editor = $app['experimentEditor'];

    $visibility = null;

    if ($request->request->get('visibility')) {
        $visibility = $request->request->get('visibility');
    }

	if ($stage = $request->request->get('stage')) {

		$experimentId = $editor->createNew($stage, $visibility);

	} else {

		$experimentId = $editor->createNew(false, $visibility);

	} 

    // Now autopublish experiment
    if (getenv('experiments_publish') == "on_creation") {
        $editor = $app['experimentEditor'];
        $editor->setExperimentById($experimentId);
        $editor->publishExperiment();

    }


	$response['experiment']['experiment_id'] = $experimentId;
	$response['status'] = "success";

	return $app->json($response);

})->before($authRequired);


$agitator->patch('/experiments/{experimentId}/settings/', function ($experimentId, Request $request) use ($app) {

	$interactor = $app['experimentInteractor'];
	$interactor->setExperimentById($experimentId);

	$editor = new Karolina\Experiment\ExperimentEditor(
		$app['currentUser'],
		$interactor,
		new Karolina\Experiment\ExperimentRepository($app['ci']),
        $app['platform']
		);

	$newSettings = $request->request->get('settings');

	$editor->updateSettings($newSettings);

	$response['status'] = "success";
	$response['message'] = "Updated settings";

	return $app->json($response);

})->before($authRequired);


$agitator->patch('/experiments/{experimentId}/image_collection/', function ($experimentId, Request $request) use ($app) {

	$interactor = $app['experimentInteractor'];
	$interactor->setExperimentById($experimentId);

    $editor = new Karolina\Experiment\ExperimentEditor(
		$app['currentUser'],
		$interactor,
		new Karolina\Experiment\ExperimentRepository($app['ci']),
        $app['platform']
		);

	$imageCollectionData = $request->request->get('image_collection');
	$editor->updateImages($imageCollectionData);

	$response['status'] = "success";

	return $app->json($response);

})->before($authRequired);

$agitator->patch('/experiments/{experimentId}/custom_language/{langCode}/', function ($experimentId, $langCode, Request $request) use ($app) {

	$interactor = $app['experimentInteractor'];
	$interactor->setExperimentById($experimentId);

    $editor = new Karolina\Experiment\ExperimentEditor(
		$app['currentUser'],
		$interactor,
		new Karolina\Experiment\ExperimentRepository($app['ci']),
        $app['platform']
		);


	$imageFileRepository = new \Karolina\Image\ImageFileRepository($app['s3']);

	$encode = new Karolina\Language\Base64InlineEncodingToFile($imageFileRepository, $app['platform']->conf('key'), $app['platform']->getImgStorageUrl());

	$customLanguage = $request->request->get('custom_language');
	foreach ($customLanguage as $key => $field) {
		if ($field['format'] === "html") {

			$customLanguage[$key]['value'] = $encode->processImages($field['value']);

		}
	}
	$editor->updateCustomLanguage($customLanguage, $langCode);

	$response['status'] = "success";

	return $app->json($response);


})->before($authRequired);


$agitator->patch('/experiments/{experimentId}/language/{langCode}/', function ($experimentId, $langCode, Request $request) use ($app) {

	$repository = new Karolina\Experiment\ExperimentRepository($app['ci']);
	$interactor = new Karolina\Experiment\ExperimentInteractor($repository);

	$interactor->setExperimentById($experimentId);

    $editor = new Karolina\Experiment\ExperimentEditor(
		$app['currentUser'],
		$interactor,
		new Karolina\Experiment\ExperimentRepository($app['ci']),
        $app['platform']
		);

	$language = $request->request->get('language');

	$editor->updateDefaultLanguage($language, $langCode);

	$response['status'] = "success";

	return $app->json($response);


})->before($authRequired);




$agitator->patch('/experiments/{experimentId}/language/', function ($experimentId, Request $request) use ($app) {

	$repository = new Karolina\Experiment\ExperimentRepository($app['ci']);
	$interactor = new Karolina\Experiment\ExperimentInteractor($repository);

	$interactor->setExperimentById($experimentId);

    $editor = new Karolina\Experiment\ExperimentEditor(
		$app['currentUser'],
		$interactor,
		new Karolina\Experiment\ExperimentRepository($app['ci']),
        $app['platform']
		);

	$languages = $request->request->get('languages');

    foreach ($languages as $langCode => $language) {
        $editor->updateDefaultLanguage($language, $langCode);
    }

	$response['status'] = "success";

	return $app->json($response);


})->before($authRequired);


return $agitator;
