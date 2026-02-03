<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Ramsey\Uuid\Uuid;


use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VideoGrant;

$agitator = $app['controllers_factory'];

$agitator->get('/page/metadata/', function (Request $request) use ($app) {

    $url = (string) $request->get('url');

    // Rate limit to avoid abuse, max 120 requests per minute
    $event = 'fetch_link_metadata';
    $app['platform']->limit($event, 120, 60);


    $cacheRepository = new \Karolina\CacheRepository();
    $cacheKey = 'link_metadata_'.md5($url);
    $cached = $cacheRepository->get($cacheKey);
    if ($cached) {
        $response['cached'] = true;
        $response['metadata_raw'] = json_decode($cached, true);
        $response['metadata'] = (new \Karolina\API\v1\PageMetadataResponse(json_decode($cached, true), $url))->get();
        $response['status'] = "success";
        return $app->json($response);
    }

    $link = new \Karolina\Content\Link(urldecode($url));
    $linkMeta = new \Karolina\Content\LinkMeta($link->getUrl());

    $linkMeta->fetch()->parse();
    $cacheRepository->store($cacheKey, json_encode($linkMeta->getMetadata()), 5); // Cache for 1 day = 86400 seconds

    $response['metadata_raw'] = $linkMeta->getMetadata();
    $response['metadata'] = (new \Karolina\API\v1\PageMetadataResponse($linkMeta->getMetadata(), $url))->get();
    $response['status'] = "success";
    $response['cached'] = false;

    return $app->json($response);

    
});

$agitator->get('/platform/stats/{day}/', function ($day, Request $request) use ($app) {

    $currentUser = $app['currentUser'];

    $day = (string) $day;
    $d = \DateTime::createFromFormat('Y-m-d', $day);
    if (!$d || $d->format('Y-m-d') !== $day) {
        throw new NotFoundHttpException('Invalid date format, expected YYYY-MM-DD');
    }
    $day = $d->format('Y-m-d');
    
    $statRecord = new \Karolina\Stats\Record();

    $response['stats']['ping']['users'] = $statRecord->getPing($day);
    $response['stats']['day'] = $day;


	$response['status'] = "success";  
    return $app->json($response);

    
})->before($adminOnly);

$agitator->get('/platform/ping/', function (Request $request) use ($app) {

    $currentUser = $app['currentUser'];
    
    $statRecord = new \Karolina\Stats\Record();
    $statRecord->addPing($currentUser ? $currentUser->getId() : null);

	$response['status'] = "success";  
    return $app->json($response);

    
});

$agitator->post('/tags/{langCode}', function ($langCode, Request $request) use ($app) {

    $tagRepository = new \Karolina\Tag\TagRepository();

    $tagLabel = (string) $request->request->get('label');
    $tagTypes = (array) $request->request->get('types');

    $tag = new Karolina\Tag\Tag();
    $tag->setLabel($tagLabel, $langCode);
    $tag->setTypes($tagTypes);
    $tagRepository->save($tag);


    $response['status'] = "success";
    return $app->json($response);

})->before($adminOnly);


$agitator->post('/custom_tags/{langCode}', function ($langCode, Request $request) use ($app) {

    $tagRepository = new \Karolina\Tag\TagRepository();

    $tagLabel = (string) $request->request->get('label');

    $tag = new Karolina\Tag\Tag();
    $tag->setLabel($tagLabel, $langCode);

    $tagId = $tagRepository->save($tag);


    $response['status'] = "success";
    $response['tag_id'] = $tagId;
    return $app->json($response);

})->before($authRequired);


$agitator->put('/tags/{tagId}/{langCode}', function ($tagId, $langCode, Request $request) use ($app) {

    $tagRepository = new \Karolina\Tag\TagRepository();

    $tagLabel = (string) $request->request->get('label');
    $tagType = (string) $request->request->get('type');
    $tagTypes = $request->request->get('types');


    $tag = $tagRepository->getById($tagId);

    if ($tagLabel) {
        $tag->setLabel($tagLabel, $langCode);
    }
    if ($tagType) {
        $tag->setType($tagType);
    }

    $tag->setTypes($tagTypes);

    $tagRepository->save($tag);

    $response['types'] = $tag->getTypes();
    $response['status'] = "success";
    return $app->json($response);

})->before($adminOnly);

$agitator->delete('/tags/{tagId}/', function ($tagId, Request $request) use ($app) {

    $tagRepository = new \Karolina\Tag\TagRepository();

    $tag = $tagRepository->getById($tagId);
    $tag->disable();
    $tagRepository->save($tag);

    $response['status'] = "success";
    return $app->json($response);

})->before($adminOnly);



$agitator->get('/tags/{langCode}', function ($langCode, Request $request) use ($app) {

	$tagRepository = new \Karolina\Tag\TagRepository();

	$allTags = $tagRepository->getAll();

	$tagsResponse = array();

	foreach ($allTags as $tag) {

		$tagResponse['id'] = $tag->getId();
		$tagResponse['label'] = $tag->getLabel($langCode);
        $tagResponse['type'] = $tag->getType();
        $tagResponse['types'] = $tag->getTypes();
		$tagsResponse[] = $tagResponse;

	}

	$response['tags'] = $tagsResponse;

	$response['status'] = "success";
	return $app->json($response);

});


$agitator->post('/send_email_in_queue/', function (Request $request) use ($app) {

    // Confirm the subscription from Amazon SES
    $data = json_decode($request->getContent(), true);

    if ($data['Type'] == 'SubscriptionConfirmation') {

        $notificationService = $app['notificationService'];

        $notificationService->setSubject("Please confirm the Amazon SES subscription");
        $notificationService->setBodyFromPlaintext($data['SubscribeURL']);
        $notificationService->send();

        $response['status'] = "sent";
        return $app->json($response);    

    }

    if ($data['Type'] == 'Notification') {

        $message = json_decode($data['Message'], true);
        $uuid = $message['uuid'];
    
        $notificationService = $app['notificationService'];
        $report = $notificationService->sendEmailInQueue($uuid);
    
        $response['status'] = "success";
        $response['report'] = $report;
        return $app->json($response);    

    }
  
    
}); 

/*
$agitator->get('/send_email/{uuid}/', function ($uuid, Request $request) use ($app) {

    $notificationService = $app['notificationService'];

    $report = $notificationService->sendEmailInQueue($uuid);


    $response['status'] = "success";
    $response['report'] = $report;
    return $app->json($response);    
  
    
}); 
*/




$agitator->post('/survey_results/', function (Request $request) use ($app) {


    $repo = new Karolina\Question\SurveyRepository();

    $answers = $request->get('answers');
    $questions = $request->get('questions');
    $surveyName = $request->get('survey_name');

    $repo->submitResults($answers, $questions, $surveyName);


    $response['status'] = "success";
    return $app->json($response);    
  
    
});




$agitator->get('/survey_results/{surveyName}/', function ($surveyName, Request $request) use ($app) {


    $repo = new Karolina\Question\SurveyRepository();

    $results = $repo->getResults($surveyName);

    $response['results'] = $results;

    $response['status'] = "success";
    return $app->json($response);    
  
    
})->before($authRequired);


$agitator->delete('/calendar/events/{eventId}/', function ($eventId, Request $request) use ($app) {

    $interactor = new Karolina\Happening\CalendarInteractor();

    $event = $interactor->getEventById($eventId);

    $user = $app['currentUser'];

    if ($user->getId() == $event->getUserId() or $user->isAdmin()) {
        $interactor->deactivateEvent($eventId);
    }

    $response['status'] = "success";
    return $app->json($response);    
  
    
})->before($authRequired);

$agitator->post('/calendar/activites/events/', function (Request $request) use ($app) {


    $interactor = new Karolina\Happening\CalendarInteractor();

    $start = $request->get('start');
    $end = $request->get('end');
    $calendars = array(
        'activities'
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

$agitator->get('/calendar/events/participate/', function (Request $request) use ($app) {

    $interactor = new Karolina\Happening\CalendarInteractor();

    $events = $interactor->getEvents('activities');
    $responses = [];
    foreach ($events as $event) {
        $responses[] = (new \Karolina\API\v1\CalendarEventResponse($event))->get();
    }

    $response['calendar_events'] = $responses;
    $response['status'] = "success";
    return $app->json($response);

});


$agitator->post('/calendar/events/attendance/{eventId}/me/', function ($eventId, Request $request) use ($app) {

    $interactor = new Karolina\Happening\CalendarInteractor();
    $event = $interactor->getEventById($eventId);

    $event->addAttending($app['currentUser']->getId());
    $interactor->saveEvent($event);

    $response['status'] = "success";
    return $app->json($response);

})->before($authRequired);


$agitator->post('/calendar/events/attendance/{eventId}/me/{plus}/', function ($plus, $eventId, Request $request) use ($app) {

    $plus = (int) $plus;

    $interactor = new Karolina\Happening\CalendarInteractor();
    $event = $interactor->getEventById($eventId);

    $event->addPlus($app['currentUser']->getId(), $plus);
    $interactor->saveEvent($event);

    $response['status'] = "success";
    return $app->json($response);

})->before($authRequired);

$agitator->delete('/calendar/events/attendance/{eventId}/me/', function ($eventId, Request $request) use ($app) {


    $interactor = new Karolina\Happening\CalendarInteractor();
    $event = $interactor->getEventById($eventId);

    $event->removeAttending($app['currentUser']->getId());
    $interactor->saveEvent($event);

    $response['status'] = "success";
    return $app->json($response);

})->before($authRequired);



$agitator->get('/calendar/make_events/', function (Request $request) use ($app) {

    $interactor = new Karolina\Happening\CalendarInteractor();

    $event = new Karolina\Happening\CalendarEvent(
        '2022-10-29 12:30',
        '2022-10-30 13:30',
        ['activities']
    );

    $event->setPrice('2100', 'isk');

    $interactor->saveEvent($event);

    return "jeps";


});



$agitator->get('/stats/{scope}/', function ($scope, Request $request) use ($app) {

    $experiments = new Karolina\Experiment\ExperimentRepository();
    $comments = new Karolina\Discussion\DiscussionRepository();
    $users = new Karolina\User\UserRepository();
    $network = new Karolina\Network\NetworkRepository();

    $response['stats']['experiments'] = $experiments->getStats($scope);
    $response['stats']['experiments']['relationships'] = $network->getStats('experiments', 'users', $scope);
    $response['stats']['comments'] = $comments->getStats($scope);
    $response['stats']['users'] = $users->getUserStats($scope);

    $response['status'] = "success";
    return $app->json($response);

});


$agitator->patch('/platform/', function (Request $request) use ($app) {

    $platform = $app['platform'];

    if ($site_title = $request->get('site_title'))  $platform->setSiteTitle($site_title);
    if ($site_label = $request->get('site_label'))  $platform->setSiteLabel($site_label);
    if ($mission_statement = $request->get('mission_statement'))  $platform->setMissionStatement($mission_statement);
    if ($languages = $request->get('site_languages'))  $platform->setLanguages($languages);
    if ($header_elements = $request->get('header_elements'))  $platform->setHeaderElements($header_elements);
    if ($platform_logo = $request->get('platform_logo') or $request->get('platform_logo') === '')  $platform->setPlatformLogo($platform_logo);
    
    if ($access = $request->get('access')) $platform->setSiteAccess($access['site']);


    $secret_access_code_active = $request->get('secret_access_code_active');
    $platform->setSecretAccessCodeActive($secret_access_code_active);

    $response['status'] = "success";
    return $app->json($response);

})->before($adminOnly);


$agitator->get('/platforms/', function (Request $request) use ($app) {

    $repo = new \Karolina\PlatformRepository();

    $platforms = $repo->getPlatforms();

    foreach ($platforms as $platform) {

        $response['platforms'][] = $platform->getSiteLabel();

    }
    $response['status'] = "success";
    return $app->json($response);

});


$agitator->post('/platform/', function (Request $request) use ($app) {

    $data['platform_key'] = $request->request->get('platform_key');
    $data['admin']['email'] = $request->request->get('admin_email');
    $data['admin']['first_name'] = $request->request->get('admin_first_name');
    $data['admin']['last_name'] = $request->request->get('admin_last_name');
    $data['admin']['password'] = $request->request->get('admin_password');

    $newPlatformAction = new \Karolina\PlatformNewAction($data, $app['notificationService']);

    $response['status'] = 'success';
    return $app->json($response);

});


$agitator->get('/platform/', function (Request $request) use ($app) {

    $settingRepository = new Karolina\Setting\SettingRepository();

    $platform = $app['platform'];
    $response['platform'] = (new \Karolina\API\v1\PlatformResponse($platform))->get();
    $response['ui'] = (new \Karolina\API\v1\PlatformResponse($platform))->getUi();
    $response['translations'] = $settingRepository->getAllTranslations();
    $response['status'] = "success";
    return $app->json($response);

});

$agitator->get('/platform/config/', function (Request $request) use ($app) {

    $platform = $app['platform'];

    $response['config'] = (new \Karolina\API\v1\PlatformResponse($platform))->getConfigRaw();

    return $app->json($response);

})->before($adminOnly);



$agitator->put('/platform/config/', function (Request $request) use ($app) {

    $key = $request->get('key');
    $value = $request->get('value');
    $platform = $app['platform'];

    $key = \Karolina\Keyname::validateConfigKey($key);

    $validator = new \Opis\JsonSchema\Validator();

    // read json file contents
    $schemaJson = file_get_contents('../../config/config_item_schemas/'.$key.'.json');
    $schema = json_decode($schemaJson);

    /*
    if (is_array($value)) {
        $value = (object)$value;
    }
    */ 
    
    $result = $validator->dataValidation($value, $schema);

    if ($result->isValid()) {
    
        $platform->setConfPublic($key, $value);

        $response['status'] = 'sucess';    
        return $app->json($response);
    }
    
    if ($result->hasErrors()) {

        $errors = "";
        // Listing errors
        foreach($result->getErrors() as $error) {
            $errors .= $error->keyword() . " " . print_r($error->keywordArgs(), true) . " " . "\n";
        }
        throw new Exception($errors);
    }


})->before($adminOnly);


$agitator->get('/platform/config/', function (Request $request) use ($app) {

    $platform = $app['platform'];

    $response['config'] = (new \Karolina\API\v1\PlatformResponse($platform))->getConfigRaw();

    return $app->json($response);

})->before($adminOnly);


$agitator->put('/platform/translations/{lang}/', function ($lang, Request $request) use ($app) {

    $settingRepository = new Karolina\Setting\SettingRepository();

    $settingRepository->updateTranslation((string) $request->get('key'), (string)  $lang, (string)  $request->get('value'));
    
    $response['status'] = "success";
    return $app->json($response);

})->before($adminOnly);

$agitator->get('/platform/admin/', function (Request $request) use ($app) {

    $platform = $app['platform'];
    $response['platform'] = (new \Karolina\API\v1\PlatformResponse($platform))->getAdmin();
    $response['status'] = "success";
    return $app->json($response);

})->before($adminOnly);


$agitator->get('/sdg/tags/{langCode}', function ($langCode, Request $request) use ($app) {

    $tagRepository = new \Karolina\Tag\TagRepository();

    $allTags = $tagRepository->getAll('sdg');

    $tagsResponse = array();

    foreach ($allTags as $tag) {

        $tagResponse['id'] = $tag->getId();
        $tagResponse['label'] = $tag->getLabel($langCode);
        $tagResponse['type'] = $tag->getType();
        $tagsResponse[] = $tagResponse;

    }

    $response['tags'] = $tagsResponse;
    $response['status'] = "success";

    return $app->json($response);

});

$agitator->get('/tags/{type}/{langCode}', function ($type, $langCode, Request $request) use ($app) {

    $tagRepository = new \Karolina\Tag\TagRepository();

    $allTags = $tagRepository->getAll($type);

    $tagsResponse = array();

    foreach ($allTags as $tag) {

        $tagResponse['id'] = $tag->getId();
        $tagResponse['label'] = $tag->getLabel($langCode);
        $tagResponse['type'] = $tag->getType();
        $tagsResponse[] = $tagResponse;

    }

    $response['tags'] = $tagsResponse;
    $response['status'] = "success";

    return $app->json($response);

});

$agitator->post('/events/last_activity_fetch/', function (Request $request) use ($app) {


    if ($user = $app['currentUser']) {

        $userEditor = new \Karolina\User\UserEditor(new \Karolina\User\UserRepository($app['ci']));
        $userEditor->setUser($app['currentUser']);
        $settings['last_activity_fetch'] = time();
        $userEditor->directUpdateSettings($settings);
    }

    $response['status'] = "success";
    return $app->json($response);
    
});


$agitator->get('/events/', function (Request $request) use ($app) {

    $repo = new \Karolina\Event\EventRepository();

    $response['events'] = (new \Karolina\API\v1\EventResponse($repo->getEvents(), $app['platform']))->get();
    $response['status'] = "success";
    return $app->json($response);

});

$agitator->get('/events/since/{time}/', function ($time, Request $request) use ($app) {

    if ($time == "my_last_fetched") {
        $user = $app['currentUser'];
        if ($user->settingIsSet('last_activity_fetch')) {
            $time = $user->setting('last_activity_fetch');
            $actorId = $user->getId();
        } else {
            $time = 0;
            $actorId = 0;
        }
    }
    $time = (int) $time;
    $repo = new \Karolina\Event\EventRepository();

    $back = time() - 604800; // Seven days back
    $response['count'] = $repo->countEventsSince($time, $back, $actorId);
    $response['status'] = "success";

    return $app->json($response);

});




$agitator->get('/dashboard/', function (Request $request) use ($app) {

    $dashboard = new \Karolina\Stats\Dashboard($app['experimentInteractor'], $app['userInteractor']);

    return $app->json($dashboard->getStats('index'));

});

$agitator->post('/files/images/', function (Request $request) use ($app) {

	$file = $request->files->get('file');

	$imageFileRepository = new Karolina\Image\ImageFileRepository($app['s3']);

	$destinationFolder = $app['platform']->conf('key');

	$filename = $imageFileRepository->storeImageFile($file, $destinationFolder);
	$response['image']['filename'] = $filename;

	$imgStorageUrl = $app['platform']->getImgStorageUrl();
	$response['file']['url'] = $imgStorageUrl.'/'.$filename;

	$response['status'] = "success";

	return $app->json($response);


})->before($authRequired);





$agitator->post('/contact/', function (Request $request) use ($app) {

	$notificationService = $app['notificationService'];
	$platform = $app['platform'];

	$data = $request->request->get('data');
	$data['platform_name'] = $platform->conf('name');

	$notificationService->setSubjectFromTemplate('contact_notification_subject', $data);
	$notificationService->setBodyFromTemplate('contact_notification_body', $data);	
	$notificationService->addCCEmail($data['email']);
	$notificationService->send();

	$response['status'] = "success";

	return $app->json($response);


});


return $agitator;
