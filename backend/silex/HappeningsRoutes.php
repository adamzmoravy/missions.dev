<?php

use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Ramsey\Uuid\Uuid;

use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VideoGrant;

$agitator = $app['controllers_factory'];

$agitator->get('/calendar/events/', function (Request $request) use ($app) {

    $interactor = new Karolina\Happening\CalendarInteractor();

    $events = $interactor->getEvents();
    $responses = [];
    foreach ($events as $event) {

        if ($event->isInGroups($app['currentUser']->getHappeningIdsRegisteredTo())) {
    
            $responses[] = (new \Karolina\API\v1\CalendarEventResponse($event))->get();

        }

    }

    $response['calendar_events'] = $responses;
    $response['status'] = "success";
    return $app->json($response);

});


$agitator->get('/happenings/{happeningId}/calendar/events/', function ($happeningId, Request $request) use ($app) {

    $happeningInteractor = $app['happeningInteractor'];
    $happening = $happeningInteractor->getHappening($happeningId);

    if ($happening->isUserRegistered($app['currentUser']->getId()) or $app['currentUser']->isAdmin()) {

        $interactor = new Karolina\Happening\CalendarInteractor();

        $events = $interactor->getEvents('happening-'.$happeningId);
        $responses = [];
        foreach ($events as $event) {
            $responses[] = (new \Karolina\API\v1\CalendarEventResponse($event))->get();
        }

        $response['calendar_events'] = $responses;
        $response['status'] = "success";
        return $app->json($response);

    } else {

        throw new Karolina\Exception('You need to be part of group to do this', 'access_denied', 500);

    }
});


$agitator->get('/happenings/{happeningId}/calendar/past_events/{backToDate}/', function ($happeningId, $backToDate, Request $request) use ($app) {

    $happeningInteractor = $app['happeningInteractor'];
    $happening = $happeningInteractor->getHappening($happeningId);

    if ($happening->isUserRegistered($app['currentUser']->getId()) or $app['currentUser']->isAdmin()) {

        $interactor = new Karolina\Happening\CalendarInteractor();

        $events = $interactor->getPastEvents('happening-'.$happeningId, $backToDate);
        $responses = [];
        foreach ($events as $event) {
            $responses[] = (new \Karolina\API\v1\CalendarEventResponse($event))->get();
        }

        $response['calendar_events'] = $responses;
        $response['status'] = "success";
        return $app->json($response);

    } else {

        throw new Karolina\Exception('You need to be part of group to do this', 'access_denied', 500);

    }
});



// Special route for displaying events on monitors in hafnar.haus - only relevant for this

$agitator->get('/monitor/haus/calendar/events/', function (Request $request) use ($app) {

    $happeningId = "37";

    $happeningInteractor = $app['happeningInteractor'];
    $happening = $happeningInteractor->getHappening($happeningId);

    $interactor = new Karolina\Happening\CalendarInteractor();

    $events = $interactor->getEvents('happening-'.$happeningId);
    $responses = [];
    foreach ($events as $event) {
        $responses[] = (new \Karolina\API\v1\CalendarEventResponse($event))->get();
    }

    $response['calendar_events'] = $responses;
    $response['status'] = "success";
    return $app->json($response);

});


$agitator->post('/happenings/{happeningId}/calendar/events/', function ($happeningId, Request $request) use ($app) {

    if (!$app['currentUser'] and getenv('access') == 'members_only') {
        throw new Karolina\Exception('You need to be logged in to see this', 'access_denied', 500);
    }

    /* @var $interactor \Karolina\Happening\HappeningInteractor */
    $happeningInteractor = $app['happeningInteractor'];
    $happening = $happeningInteractor->getHappening($happeningId);

    if ($happening->isUserRegistered($app['currentUser']->getId()) or $app['currentUser']->isAdmin()) {

        $interactor = new Karolina\Happening\CalendarInteractor();

        $start = $request->get('start');
        $end = $request->get('end');
        $calendars = array(
            'happening-'.$happeningId
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
    } else {

        throw new Karolina\Exception('You need to be part of group to do this', 'access_denied', 500);
    }
    
})->before($authRequired);

$agitator->patch('/happenings/{happeningId}/custom_language/{langCode}/', function ($happeningId, $langCode, Request $request) use ($app) {

    $interactor = $app['happeningInteractor'];

    $imageFileRepository = new \Karolina\Image\ImageFileRepository($app['s3']);

    $encode = new Karolina\Language\Base64InlineEncodingToFile($imageFileRepository, $app['platform']->conf('key'), $app['platform']->getImgStorageUrl());

    $customLanguage = $request->request->get('custom_language');
    foreach ($customLanguage as $key => $field) {
        if ($field['format'] === "html") {

            $customLanguage[$key]['value'] = $encode->processImages($field['value']);

        }
    }

    $interactor->updateCustomLanguage($happeningId, $customLanguage, $langCode);

    $response['status'] = "success";

    return $app->json($response);


})->before($adminOnly);

// Depricated - to be removed later
$agitator->get('/happenings/{happeningId}/video_chat/', function ($happeningId, Request $request) use ($app) {

    if ($app['currentUser']) {
        $identity = $app['currentUser']->getId();
    } else {
        throw new \Karolina\Exception('You need to be logged in to access video room', 'access_denied');
    }

    $interactor = $app['happeningInteractor'];

    $happening = $interactor->getHappening($happeningId);
    $room = getenv('platform_key').',happening,'.$happeningId.','.$happening->getUUID();


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

// Depricated - to be removed later
$agitator->post('/video_chat/webhook/', function (Request $request) use ($app) {

        $event = $request->request->get('StatusCallbackEvent');

        if ($event == "room-ended" or $event == "room-created") {

            $roomName = $request->request->get('RoomName');
            $roomArray = explode(",", $roomName);
            $roomData['platform'] = $roomArray[0];

            $options = array(
                'cluster' => 'eu',
                'useTLS' => true
            );
            $pusher = new \Pusher\Pusher(
                getenv('pusher_api_key'),
                getenv('pusher_secret'),
                getenv('pusher_app_id'),
                $options
            );

            $data['message'] = 'Meeting room has changed';
            $pusher->trigger($roomData['platform'], 'video-room-change', $data);
        }

        $response['event'] = $event;
        $response['status'] = "success";
        return $app->json($response);

});

// Depricated - to be removed later

$agitator->get('/video_chat/{retrieve}/', function ($retrieve, Request $request) use ($app) {

    // Substitute your Twilio Account SID and API Key details
    $accountSid = getenv('twilio_account');
    $apiKeySid = getenv('twilio_api');
    $apiKeySecret = getenv('twilio_secret');
    $authToken = getenv('twilio_auth_token');

    $twilio = new Twilio\Rest\Client($accountSid, $authToken);
    $rooms = $twilio->video->v1->rooms
        ->read(["status" => "in-progress"], 100);

    $roomsData = array();

    foreach ($rooms as $room) {


        $roomArray = explode(",", $room->uniqueName);
        $roomData['platform'] = $roomArray[0];
        $roomData['object_type'] = $roomArray[1];
        $roomData['object_id'] = $roomArray[2];
        $roomData['object_UUID'] = $roomArray[3];


        if ($roomData['platform'] == getenv('platform_key')) {

            if ($retrieve == "participants") {
                $participants = $twilio->video->rooms($room->uniqueName)
                    ->participants->read(array("status" => "connected"));

                foreach ($participants as $participant) {
                    $roomData['participants'] = array();
                    $roomData['participants'][] = $participant->identity;
                }

            }

            $roomsData[] = $roomData;

        }

    }

    $response['rooms'] = $roomsData;
    $response['status'] = "success";
    return $app->json($response);


});



$agitator->get('/happenings/', function (Request $request) use ($app) {

    $currentUser = $app['currentUser'];

    if (!$app['currentUser'] and getenv('access') == 'members_only') {
        throw new Karolina\Exception('You need to be logged in to see this', 'access_denied', 500);
    }

    $repo = new \Karolina\Happening\HappeningRepository();
    $happenings = $repo->getAllHappenings();
    $interactor = $app['happeningInteractor'];

    $response['happenings'] = [];
    foreach ($happenings as $happening) {

        $happeningDoc = $interactor->getHappeningData($happening);
        $response['happenings'][] = (new Karolina\API\v1\HappeningResponse($happeningDoc, $app['platform']))->getPreview();
    }

    $response['status'] = "success";
    return $app->json($response);

});

$agitator->post('/happenings/{happeningId}/photo_stream/', function ($happeningId, Request $request) use ($app) {

    $interactor = $app['happeningInteractor'];
    $happening = $interactor->getHappening($happeningId);

    $imageRecordRepository = new \Karolina\Image\ImageRecordRepository();

    if ($happening->isUserRegistered($app['currentUser']->getId())) {

//        $photoStream = $imageRecordRepository->getImageRecordsByContext('happening-photo-stream-'.$happeningId);

        $filename = $request->get('filename');

        $image = new \Karolina\Image\Image($filename, 'happening-photo-stream-'.$happeningId);

        $imageRecordRepository->saveImageRecord(
            $image,
            $app['currentUser']->getId(),
        );

    } else {

        $response['status'] = "error";
        $response['message'] = "You are not registered for this group";
        return $app->json($response);

    }


    $response['status'] = "success";
    return $app->json($response);

});


$agitator->get('/happenings/{happeningId}/photo_stream/{limit}/', function ($happeningId, $limit, Request $request) use ($app) {

    $limit = (int) $limit;

    $repository = new \Karolina\Discussion\DiscussionRepository();

    /* @var $interactor \Karolina\Happening\HappeningInteractor */
    $interactor = $app['happeningInteractor'];

    $happening = $interactor->getHappening($happeningId);

    $showFullInfo = true;
    if ($happening->isPrivate()) {
        $showFullInfo = false;
        if ($app['currentUser']
            && (
                $happening->isUserRegistered($app['currentUser']->getId())
            or
                $app['currentUser']->isAdmin()
            )) {
            $showFullInfo = true;
        }
    }

    $imageRecordRepository = new \Karolina\Image\ImageRecordRepository();
    $photoStream = $imageRecordRepository->getImageRecordsByContext('happening-photo-stream-'.$happeningId, $limit);

    $response['photo_stream'] = [];

//    if ($showFullInfo) {
    if (true) {
        foreach ($photoStream as $photo) {

            $photoResponse['id'] = $photo->getId();
            $photoResponse['filename'] = $photo->getFilename();
            $response['photo_stream'][] = $photoResponse;

        }
    }
    $response['status'] = "success";
    return $app->json($response);


});


$agitator->get('/happenings/topics/', function (Request $request) use ($app) {

    $response['comments'] = [];
    $commentId = $request->query->get('comment_id');

    if ($app['currentUser']) {
        $repository = new \Karolina\Discussion\DiscussionRepository();

        $currentUser = $app['currentUser'];
    
        $myTopics = $currentUser->getMyTopics();
    
        $comments = $repository->getLatestCommentsByTopics($myTopics, 5, null, $commentId);
        $imgStorageUrl = $app['platform']->getImgStorageUrl();
    
        $response['comments'] = (new Karolina\API\v1\CommentsResponse($comments, $imgStorageUrl))->get();
    
    }
    $response['status'] = "success";
    return $app->json($response);

});

$agitator->get('/happenings/{happeningId}/topic/', function ($happeningId, Request $request) use ($app) {

    if (!$app['currentUser'] and getenv('access') == 'members_only') {
        throw new Karolina\Exception('You need to be logged in to see this', 'access_denied', 500);
    }

    $limit = $request->query->get('limit');
    $offset = $request->query->get('offset');
    $commentId = $request->query->get('comment_id');

    $repository = new \Karolina\Discussion\DiscussionRepository();

    /* @var $interactor \Karolina\Happening\HappeningInteractor */
    $interactor = $app['happeningInteractor'];

    $happening = $interactor->getHappening($happeningId);

    $showFullInfo = true;
    if ($happening->isPrivate()) {
        $showFullInfo = false;
        if ($app['currentUser']
            && (
                $happening->isUserRegistered($app['currentUser']->getId())
            or
                $app['currentUser']->isAdmin()
            )) {
            $showFullInfo = true;
        }
    }

    $topicId = $happening->getUUID();

    $topic = $repository->getTopic($topicId, $limit, $offset, $commentId);
    $imgStorageUrl = $app['platform']->getImgStorageUrl();

    if ($showFullInfo) {
        $response['comments'] = (new Karolina\API\v1\CommentsResponse($topic->getComments(), $imgStorageUrl))->get();
    } else {
        $response['comments'] = [];
    }
    $response['status'] = "success";
    return $app->json($response);

});


$agitator->post('/happenings/{happeningId}/comment/{commentId}/reply/', function ($happeningId, $commentId, Request $request) use ($app) {

    $currentUser = $app['currentUser'];

    if (!$currentUser) {
        throw new  \Karolina\Exception("Must be logged in to comment", 'not_logged_in');
    }

    /* @var $interactor \Karolina\Happening\HappeningInteractor */
    $interactor = $app['happeningInteractor'];
    $happening = $interactor->getHappening($happeningId);

    $value = $request->request->get('value');

    $repository = new \Karolina\Discussion\DiscussionRepository();
    $topic = $repository->getTopic($happening->getUUID());
    $format = $request->request->get('format');
    $reply = $topic->addNewReply($value, $currentUser, 'reply', $commentId, $format);
    $comment = $repository->saveComment($reply);


    $originalComment = $repository->getComment($commentId);
    $userRepo = new \Karolina\User\UserRepository();
    $originalCommenter = $userRepo->getById($originalComment->getUserId());


    $exclude = [$currentUser->getId()];
    $conversationParticipants = $topic->getCommentConversationParticipantsIds($commentId, $exclude);

    /* @var $notification \Karolina\Notification */

    $notification = $app['notificationService'];
    $frontEndUrl = $app['platform']->getPlatformFrontendUrl();

    $data['topic_title'] = $happening->getName();
    $data['body'] = $comment->getHTMLValue($frontEndUrl);
    $data['commenter']['name'] = $currentUser->getFirstName().' '.$currentUser->getLastName();
    $data['original_body'] = $originalComment->getHTMLValue($frontEndUrl);
    $data['original_commenter']['name'] = $originalCommenter->getName();
    $data['happening_id'] = $happening->getId();
    $data['base_url'] = $app['platform']->getPlatformFrontendUrl();
    $data['commenter']['image'] = $app['platform']->getImgStorageUrl().'/'.$currentUser->getProfileImage();
    $data['commenter']['user_id'] = $currentUser->getId();

    $notification->setSubject("Reply from ".$currentUser->getFirstName()." on ".$happening->getName())
        ->setBodyFromTemplate('happening_announcement_reply', $data)
        ->setToUserIds($conversationParticipants)
        ->addToEmailQueue();

    $mentionedUserIds = $comment->getMentionedUserIds();
    if (count($mentionedUserIds)) {
        $notification->setSubject($currentUser->getFirstName().' mentioned you in a reply on '.$happening->getName())
            ->setToUserIds($mentionedUserIds)
            ->addToEmailQueue();    
    }

    $response['status'] = "success";

    return $app->json($response);

});


$agitator->post('/happenings/{happeningId}/topic/', function ($happeningId, Request $request) use ($app) {

    /* @var $interactor \Karolina\Happening\HappeningInteractor */
    $interactor = $app['happeningInteractor'];
    $happening = $interactor->getHappening($happeningId);

    $currentUser = $app['currentUser'];

    if (!$currentUser) {
        throw new  \Karolina\Exception("Must be logged in to comment", 'not_logged_in');
    }

    $action = new Karolina\Discussion\CommentToTopicAction($happening->getUUID(), $app['platform']);

    $action->createCommentFromRequest($request, $currentUser);

    $submissionsPreviews = $action->getSubmissionsFromComment($action->getComment()->getSubmissions());

    $comment = $action->saveComment();

    $type = $request->request->get('type');
    $emailAnnouncement = (boolean) $request->request->get('email_announcement');
    $calendarInvite = $request->request->get('calendar_invite');
    $includeCalendarInvitation = (Boolean) $request->request->get('include_calendar_invitation');

    $notification = $action->createNotification(
        $app['notificationService'], 
        $app['platform']->getPlatformFrontendUrl(),
        $app['platform']->getImgStorageUrl(), 
        $currentUser, 
        $happening, 
        $type, 
        $request->request->get('subject'), 
        $includeCalendarInvitation, 
        $calendarInvite,
        $submissionsPreviews
    );


    if ($emailAnnouncement) {

        $action->sendEmail($happening, $notification);

    }

    $mentionedUserIds = $comment->getMentionedUserIds();
    if (count($mentionedUserIds)) {
        $notification->setSubject($currentUser->getFirstName().' mentioned you in '.$happening->getName())
            ->setToUserIds($mentionedUserIds)
            ->addToEmailQueue();    
    }

    $addittionalData['happening_id'] = $happening->getId();
    $action->publishEvent('comment_happening', $addittionalData);

    $response['status'] = "success";
    $response['comment']['comment_id'] = $comment->getId();
    return $app->json($response);

});



$agitator->get('/happenings/{happeningId}/', function ($happeningId, Request $request) use ($app) {

    if (!$app['currentUser'] and getenv('access') == 'members_only') {
        throw new Karolina\Exception('You need to be logged in to see this', 'access_denied', 500);
    }

    /* @var $interactor \Karolina\Happening\HappeningInteractor */
    $interactor = $app['happeningInteractor'];

    $happening = $interactor->getHappening($happeningId);

    $showFullInfo = true;
    if ($happening->isPrivate()) {
        $showFullInfo = false;
        if ($app['currentUser']
            && (
                $happening->isUserRegistered($app['currentUser']->getId())
                or
                $app['currentUser']->isAdmin()
            )) {
            $showFullInfo = true;
        }
    }

    /* @var $happening \Karolina\Happening\Happening */
    $happeningDoc = $interactor->getHappeningData($happening);

    if ($showFullInfo) {

        $response['happening'] = (new Karolina\API\v1\HappeningResponse($happeningDoc, $app['platform']))->get(false, $app['userRepository']);

        $repository = new \Karolina\Discussion\DiscussionRepository();
        $topic = $repository->getTopic($happening->getUUID(), 5, 0);
        $imgStorageUrl = $app['platform']->getImgStorageUrl();
        $response['happening']['topic'] = (new Karolina\API\v1\CommentsResponse($topic->getComments(), $imgStorageUrl))->get();

    } else {

        $response['happening'] = (new Karolina\API\v1\HappeningResponse($happeningDoc, $app['platform']))->getLimited();

    }

    return $app->json($response);

});


$agitator->get('/happenings/{happeningId}/registrations/', function ($happeningId, Request $request) use ($app) {

    /* @var $currentUser Karolina\User\ */
    $currentUser = $app['currentUser'];

    if ($currentUser and $currentUser->isAdmin()) {
        /* @var \Karolina\Happening\HappeningInteractor */
        $interactor = $app['happeningInteractor'];

        /* @var $happening \Karolina\Happening\Happening */
        $happeningDoc = $interactor->getHappeningDataById($happeningId);
        $response['happening'] = (new Karolina\API\v1\HappeningResponse($happeningDoc, $app['platform']))->get(true, $app['userRepository']);

        return $app->json($response);

    } else {
        throw new \Karolina\Exception('You need to be admin to see registration details', 'admin_only');

    }

});

$agitator->post('/happenings/{happeningId}/config/', function ($happeningId, Request $request) use ($app) {

    $interactor = $app['happeningInteractor'];
    $config = $request->request->get('config');

    $interactor->setConfig($happeningId, $config);

    $response['status'] = "success";
    return $app->json($response);



})->before($adminOnly);


$agitator->patch('/happenings/{happeningId}/registrations/{userId}/', function ($happeningId, $userId,  Request $request) use ($app) {

    /* @var $currentUser Karolina\User\ */
    $currentUser = $app['currentUser'];

    if ($currentUser and $currentUser->isAdmin()) {
        /* @var \Karolina\Happening\HappeningInteractor */
        $interactor = $app['happeningInteractor'];

        $type = $request->request->get('type');
        $interactor->updateUserRegistration($happeningId, $userId, $type);

        $response['status'] = "success";
        return $app->json($response);

    } else {
        throw new \Karolina\Exception('You need to be admin to see registration details', 'admin_only');

    }

});

$agitator->delete('/happenings/{happeningId}/registrations/{userId}/', function ($happeningId, $userId,  Request $request) use ($app) {

    /* @var $currentUser Karolina\User\ */
    $currentUser = $app['currentUser'];

    if ($currentUser and ($currentUser->isAdmin() or $currentUser->getId() == $userId) ) {
        /* @var \Karolina\Happening\HappeningInteractor */
        $interactor = $app['happeningInteractor'];

        $interactor->removeUserRegistration($happeningId, $userId);

        $response['status'] = "success";
        return $app->json($response);

    } else {
        throw new \Karolina\Exception('You need to be admin to see registration details', 'admin_only');

    }

});

$agitator->delete('/happenings/{happeningId}/', function ($happeningId,  Request $request) use ($app) {

    /* @var $currentUser Karolina\User\ */
    $currentUser = $app['currentUser'];

    if ($currentUser->isAdmin()) {
        /* @var \Karolina\Happening\HappeningInteractor */
        $interactor = $app['happeningInteractor'];

        $interactor->removeHappening($happeningId);

        $response['status'] = "success";
        return $app->json($response);

    } else {
        throw new \Karolina\Exception('You need to be admin to see registration details', 'admin_only');

    }

});


$agitator->post('/happenings/{happeningId}/schedule/', function ($happeningId, Request $request) use ($app) {

    if ($currentUser = $app['currentUser']) {

        $schedule = $request->request->get('schedule');

        $app['happeningInteractor']->setSchedule($happeningId, $schedule, $app['currentUser']);
        $response['status'] = "success";

        return $app->json($response);

    } else {
        throw new \Karolina\Exception('You need to be logged in change the schedule', 'need_logged_in_to_schedule');
    }


});


// New application
$agitator->post('/happenings/{happeningId}/applications/', function ($happeningId, Request $request) use ($app) {

    if ($currentUser = $app['currentUser']) {
        $userId = $currentUser->getId();
    } else {
        $userId = NULL;
    }

    $applicationInfoData = $request->request->get('application_info');
    $happening = $app['happeningInteractor']->getHappening($happeningId);

    if ($happening->isAcceptingApplications()) {

        $app['happeningInteractor']->applyToHappening($userId, $happeningId, $applicationInfoData);
        $response['status'] = "success";
        return $app->json($response);

    } else {
        throw new \Karolina\Exception('Groups is not accepting applications');
    }



});


$agitator->post('/happenings/{happeningId}/registrations/', function ($happeningId, Request $request) use ($app) {

    if ($currentUser = $app['currentUser']) {

        $registrationInfoData = $request->request->get('registration_info');

        $userId = $currentUser->getId();

        $happening = $app['happeningInteractor']->getHappening($happeningId);

        if ($happening->isAcceptingRegistrations()) {

            $app['happeningInteractor']->registerUserToHappening($userId, $happeningId, $registrationInfoData);
            $response['status'] = "success";
            return $app->json($response);

        } else {
            throw new \Karolina\Exception('Group is not accepting registrations');
        }



    } else {
        throw new \Karolina\Exception('You need to be logged in to register to a happening', 'need_logged_in_to_register');
    }


});


$agitator->post('/happenings/{happeningId}/registrations/{userId}/', function ($happeningId, $userId, Request $request) use ($app) {

    $app['happeningInteractor']->registerUserToHappening($userId, $happeningId);
    $response['status'] = "success";

    return $app->json($response);


})->before($adminOnly);


$agitator->post('/happenings/', function (Request $request) use ($app) {

    /* @var $interactor  \Karolina\Happening\HappeningInteractor */
    $interactor = $app['happeningInteractor'];

    $data['name'] = $request->request->get('name');
    $happeningId = $interactor->createHappening($data);

    $response['happening_id'] = $happeningId;
    $response['status'] = "success";

    return $app->json($response);

})->before($adminOnly);


return $agitator;
