<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;

use Ramsey\Uuid\Uuid;

$agitator = $app['controllers_factory'];


$agitator->get('/users/settings/{token}/', function ($token, Request $request) use ($app) {

	// update settings
    $start = microtime(true);

		$userEditor = new \Karolina\User\UserEditor(new \Karolina\User\UserRepository());
		$userEditor->setUserById(1);
		$settings['go_fetcher'] = time();
		$userEditor->updateSettings($settings);

    $time_elapsed_secs = microtime(true) - $start;

    echo $time_elapsed_secs;

});


$agitator->get('/users/identifier/{email}/', function ($email, Request $request) use ($app) {

	$userRepository = new \Karolina\User\UserRepository();

	$response['exists'] = true; 
	try {

		$userRepository->getByIdentifier($email);

	} catch (Exception $e) {

		$response['exists'] = false; 

	}

    $response['status'] = "success";
    return $app->json($response);

});


$agitator->get('/users/identifier/', function (Request $request) use ($app) {

	$email = urldecode($request->query->get('email'));

	$userRepository = new \Karolina\User\UserRepository();

	$response['exists'] = true; 
	try {

		$userRepository->getByIdentifier($email);

	} catch (Exception $e) {

		$response['exists'] = false; 

	}

    $response['status'] = "success";
    return $app->json($response);

});


$agitator->get('/users/tags/{langCode}', function ($langCode, Request $request) use ($app) {

    $tagRepository = new \Karolina\Tag\TagRepository();

    $allTags = $tagRepository->getAll();

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


$agitator->get('/users/skills/', function (Request $request) use ($app) {


	$userRepository = new \Karolina\User\UserRepository();

	$allSkills = $userRepository->getAllSkills();

	$response['skills'] = $allSkills;
	$response['status'] = "success";
	return $app->json($response);


});


$agitator->post('/users/tokens/', function (Request $request) use ($app) {

	$auth = $app['authInteractor'];
	$email = $request->request->get('email');
	$password = $request->request->get('password');

	$auth->setEmailAndPassword($email, $password);

	$ttl = 86400 * 6 * 30; // Six months
//	$ttl = 5;
	$token = $auth->getBearerTokenIfAuthenticated($ttl);

	$responseData['token']['string'] = $token;
	$responseData['status'] = "success";

	return $app->json($responseData);

});




// Create new user
$agitator->post('/users/', function (Request $request) use ($app) {

	$registrationsEnabled = $app['platform']->getSiteRegistrationEnabled();

    if (!$registrationsEnabled) {

        if ($app['currentUser'] and $app['currentUser']->isAdmin()) {

            // Ok, admin can create new user

        } else if ($app['platform']->getSecretAccessCodeActive() === true && $request->request->get('access_code')) {

			// Ok, platform is in secret access mode
			if ($request->request->get('access_code') === $app['platform']->getSecretAccessCode()) {
				// Ok, we have da code!
				
			} else {
				throw new Karolina\Exception('Incorrect access code', 'access_denied', 500);
			}
		}
		else {
            throw new Karolina\Exception('Registrations disabled', 'access_denied', 500);
        }
    }

	$password = $request->request->get('password');
	$firstName = $request->request->get('first_name');
	$lastName = $request->request->get('last_name');
	$email = $request->request->get('email');

	$user = new \Karolina\User\User(null, $email, $firstName, $lastName);
	$user->setNewPassword($password);

	$userRepository = new \Karolina\User\UserRepository($app['ci']);

    [$token, $user] = $userRepository->createNew($user);

    Karolina\Event\Publisher::instance()->publish(new \Karolina\User\UserRegisteredEvent($user));

	// Add user to default group
	if($defaultGroup = $app['platform']->conf('platform.groups.default')) {

		try {

			$happening = $app['happeningInteractor']->getHappening($defaultGroup);

			if ($happening->isAcceptingRegistrations()) {

				$app['happeningInteractor']->registerUserToHappening($user->getId(), $defaultGroup, []);

			} 

		} catch (Exception $e) {
			// Default group not found or registration failed, continue without adding to group
		}

	}

	$response['token']['string'] = $token;
	$response['status'] = "success";
	return $app->json($response);
	
});

$agitator->delete('/users/{userId}/', function ($userId, Request $request) use ($app) {

    // ADMIN ONLY
    if ($app['currentUser'] and $app['currentUser']->isAdmin()) {
        // Ok, admin can disable user
    } else {
        throw new Karolina\Exception('Only admin can disable other users', 'access_denied', 500);
    }


    $userEditor = new \Karolina\User\UserEditor(new \Karolina\User\UserRepository());
    $userEditor->setUserById($userId);
    $userEditor->disableUser();


    $response['status'] = "success";
    $response['message'] = "User has been made inactive";

    return $app->json($response);

});


$agitator->post('/users/me/tags/', function (Request $request) use ($app) {


	$userEditor = new \Karolina\User\UserEditor(new \Karolina\User\UserRepository($app['ci']));
	$userEditor->setUser($app['currentUser']);
	$tags = $request->request->get('tags');
	$userEditor->replaceTags($tags);

	$response['status'] = "success";
	$response['message'] = "Tags have been updated.";

	return $app->json($response);


});


$agitator->post('/users/me/tags/{tagId}/', function ($tagId, Request $request) use ($app) {


	$userEditor = new \Karolina\User\UserEditor(new \Karolina\User\UserRepository());
	$userEditor->setUser($app['currentUser']);

	$tagType = $request->request->get('tag_type');

	$userEditor->addTag($tagId, $tagType);

	$response['status'] = "success";
	$response['message'] = "Tag has been added.";

	return $app->json($response);


});

$agitator->delete('/users/me/tags/{tagId}/{tagType}/', function ($tagId, $tagType, Request $request) use ($app) {


	$userEditor = new \Karolina\User\UserEditor(new \Karolina\User\UserRepository());
	$userEditor->setUser($app['currentUser']);
	$userEditor->removeTag($tagId, $tagType);

	$response['status'] = "success";
	$response['message'] = "Tag has been removed.";

	return $app->json($response);


});


$agitator->post('/users/me/skills/', function (Request $request) use ($app) {


	$userEditor = new \Karolina\User\UserEditor(new \Karolina\User\UserRepository($app['ci']));
	$userEditor->setUser($app['currentUser']);
	$skills = $request->request->get('skills');
	$userEditor->replaceSkills($skills);

	$response['status'] = "success";
	$response['message'] = "Skills have been updated.";

	return $app->json($response);


});

$agitator->post('/users/me/links/', function (Request $request) use ($app) {


	$userEditor = new \Karolina\User\UserEditor(new \Karolina\User\UserRepository($app['ci']));
	$userEditor->setUser($app['currentUser']);
	$links = $request->request->get('links');
	$userEditor->replaceLinks($links);

	$response['status'] = "success";
	$response['message'] = "Links have been updated.";

	return $app->json($response);

})->before($authRequired);


$agitator->get('/users/preview/', function (Request $request) use ($app) {

	$currentUser = $app['currentUser'];

    if (!$app['currentUser'] and getenv('access') == 'members_only') {
        throw new Karolina\Exception('You need to be logged in to see this', 'access_denied', 500);
    }

	$usersRepo = new \Karolina\User\UserRepository();

	$filterArguments = $request->query->all();
	$usersDocuments = $usersRepo->getAllDocuments($filterArguments);
	$responseArray = array();

	$imgStorageUrl = $app['platform']->getImgStorageUrl();

	foreach ($usersDocuments as $usersDocument) {

        $showPrivateInfo = false;
        if ($app['currentUser'] and $app['currentUser']->isAdmin()) {
            $showPrivateInfo = true;
        }
        $userResponse = (new \Karolina\API\v1\UserPreviewResponse($usersDocument, $imgStorageUrl))->get($showPrivateInfo);
		$responseArray[] = $userResponse;

	}

	$response['previews'] = $responseArray;

	$response['status'] = "success";
	return $app->json($response);

});

$agitator->get('/all_platforms/users/preview/', function (Request $request) use ($app) {

	$usersRepo = new \Karolina\User\UserRepository($app['ci']);

	$filterArguments = $request->query->all();

	$platforms = array(
		'hackinghekla',
		'nordkapp',
		'uw2021',
		'fieri',
		'nordurland',
		'lausnaver',
		'nyskopunargardurinn'
	);

	$usersDocuments = $usersRepo->getAllDocuments($filterArguments, $platforms);
	$responseArray = array();

	$imgStorageUrl = $app['platform']->getImgStorageUrl();

	foreach ($usersDocuments as $usersDocument) {

        $showPrivateInfo = false;
        if ($app['currentUser'] and $app['currentUser']->isAdmin()) {
            $showPrivateInfo = true;
        }
        $userResponse = (new \Karolina\API\v1\UserPreviewResponse($usersDocument, $imgStorageUrl))->get($showPrivateInfo);
		$responseArray[] = $userResponse;

	}

	$response['previews'] = $responseArray;

	$response['status'] = "success";
	return $app->json($response);

});

$agitator->get('/users/rewritedocuments/', function(Request $request) use ($app) {

	$usersRepo = new \Karolina\User\UserRepository($app['ci']);
	$documentsCount = $usersRepo->reWriteAllDocuments();

	$response['count'] = $documentsCount;
	$response['status'] = "success";

	return $app->json($response);

});


$agitator->post('/users/{userId}/contact/', function ($userId, Request $request) use ($app) {

    if ($currentUser = $app['currentUser']) {

        $event = $currentUser->getId().'-contact_request-to-anyone-ever';
        $app['platform']->limit($event, 60, 86400 * 30);

        $event = $currentUser->getId().'-contact_request-to-anyone';
        $app['platform']->limit($event, 1, 30);

        $event = $currentUser->getId().'-contact_request-to-'.$userId;
        $app['platform']->limit($event, 1, 86400 * 30);


        // Get the profile
        $getProfile = new Karolina\User\Action\GetProfile(
            $currentUser,
            new \Karolina\User\UserRepository($app['ci']),
            $app['platform']->getImgStorageUrl(),
            $app['platform']
        );
        $getProfile = $getProfile->forUserId($userId, true);
        $recipientProfile = $getProfile->getResponse();

        // Send to the recipient
        $notificationService = $app['notificationService'];
        $platform = $app['platform'];
        $data['platform_name'] = getenv('site_title');
        $data['name'] = $currentUser->getName();
        $data['message'] = substr($request->request->get('message'), 0, 400);
        $data['email'] = $recipientProfile['profile']['email'];
        $data['sender_email'] = $currentUser->getEmail();
        $data['recipient_name'] = $recipientProfile['profile']['first_name'];

        $notificationService->setSubjectFromTemplate('contact_notification_subject', $data);
        $notificationService->setBodyFromTemplate('contact_notification_body', $data);
        $notificationService->setSingleRecipientEmail($data['email']);
        $notificationService->send();

        $response['message'] = "Contact request sent";
        $response['status'] = "success";
        return $app->json($response);

    }


});

// Get user profile
$agitator->get('/users/{userId}/profile', function ($userId, Request $request) use ($app) {

				global $timings;
				$timings->start('route');

	$currentUser = $app['currentUser'];

    if (!$app['currentUser'] and getenv('access') == 'members_only') {
        throw new Karolina\Exception('You need to be logged in to see this', 'access_denied', 500);
    }

				$timings->start('Action\GetProfile');

    $getProfile = new Karolina\User\Action\GetProfile(
		$currentUser,
		new \Karolina\User\UserRepository(),
		$app['platform']->getImgStorageUrl(),
        $app['platform']
		);
				$timings->end('Action\GetProfile');

				$timings->start('getProfile');

	$getProfile = $getProfile->forUserId($userId);

				$timings->end('getProfile');

	if ($app['platform']->conf('experiments') == 'yes') {

				$timings->start('getProfileExperiments');

		$getProfile = $getProfile->withExperiments($app['experimentInteractor']);

				$timings->end('getProfileExperiments');
	}

			$timings->start('withCommentsStats');
    $getProfile = $getProfile->withCommentsStats();
			$timings->end('withCommentsStats');


    // $getProfile = $getProfile->withAcl();
			$timings->start('getResponse');
	$response = $getProfile->getResponse();
			$timings->end('getResponse');

			$timings->end('route');
	$timings->end('app');
	$response['timings'] = $timings->getTimings();
	$response['status'] = "success";
	return $app->json($response);

});



// Update current user profile
$agitator->patch('/users/me/profile', function (Request $request) use ($app) {

	if (!$app['currentUser']) {
		throw new Karolina\KarolinaException('You are not logged in', 401, null, 'invalid_login');
	}

	$userEditor = new \Karolina\User\UserEditor(new \Karolina\User\UserRepository($app['ci']));
	$userEditor->setUser($app['currentUser']);

	// Get request
	$profile['shortDescription'] = $request->request->get('short_description');
	$profile['description'] = $request->request->get('long_description');
	$profile['firstName'] = $request->request->get('first_name');
	$profile['lastName'] = $request->request->get('last_name');
	$profile['phone'] = $request->request->get('phone');
	$profile['company'] = $request->request->get('company');
	$profile['documentNumber'] = $request->request->get('document_number');
	$profile['meta'] = $request->request->get('meta');

	// Process images
	$imageFileRepository = new \Karolina\Image\ImageFileRepository($app['s3']);
	$encode = new Karolina\Language\Base64InlineEncodingToFile($imageFileRepository, $app['platform']->conf('key'), $app['platform']->getImgStorageUrl());
	$profile['description'] = $encode->processImages($profile['description']);

	$userEditor->updateProfile($profile);

	$response['status'] = "success";

	return $app->json($response);

})->before($authRequired);



// Update geographic location
$agitator->patch('/users/me/profile/geographic_location', function (Request $request) use ($app) {

	if (!$app['currentUser']) {
		throw new Karolina\KarolinaException('You are not logged in', 401, null, 'invalid_login');
	}

	$userEditor = new \Karolina\User\UserEditor(new \Karolina\User\UserRepository($app['ci']));
	$userEditor->setUser($app['currentUser']);

	// Get request
	$settings['geographic_location'] = $request->request->get('geographic_location');

	$userEditor->updateGeographicLocation($settings['geographic_location']);

	$response['status'] = "success";

	return $app->json($response);

})->before($authRequired);



// Update contact information
$agitator->post('/users/me/profile/contact_information', function (Request $request) use ($app) {

	if (!$app['currentUser']) {
		throw new Karolina\KarolinaException('You are not logged in', 401, null, 'invalid_login');
	}

	$userEditor = new \Karolina\User\UserEditor(new \Karolina\User\UserRepository($app['ci']));
	$userEditor->setUser($app['currentUser']);

	// Get request
	$settings['contact_information'] = $request->request->get('contact_information');

	$userEditor->updateContactInformation($settings['contact_information']);

	$response['status'] = "success";

	return $app->json($response);

})->before($authRequired);



// Get contact information
$agitator->get('/users/{userId}/profile/contact_information', function ($userId, Request $request) use ($app) {

	if (!$app['currentUser']) {
		throw new Karolina\KarolinaException('You are not logged in', 401, null, 'invalid_login');
	}

	$userRepository = new \Karolina\User\UserRepository($app['ci']);
	$user = $userRepository->getById($userId);
	$contactInformation = $user->getContactInformation();
	$response['contact_information'] = $contactInformation;

	$response['status'] = "success";

	return $app->json($response);

})->before($authRequired);



// Update current user profile
$agitator->patch('/users/me/profile/image_collection', function (Request $request) use ($app) {

	if (!$app['currentUser']) {
		throw new Karolina\KarolinaException('You are not logged in', 401, null, 'invalid_login');
	}

	$userEditor = new \Karolina\User\UserEditor(new \Karolina\User\UserRepository($app['ci']));
	$userEditor->setUser($app['currentUser']);


	$imagesCollection = $request->request->get('image_collection');
	$profileImageFile = $imagesCollection['profile']['filename'];

	$userEditor->updateProfileImage($profileImageFile);

	$response['status'] = "success";

	return $app->json($response);

})->before($authRequired);



// Update password
$agitator->post('/users/me/password', function (Request $request) use ($app) {

	if (!$app['currentUser']) {
		throw new Karolina\KarolinaException('You are not logged in', 401, null, 'invalid_login');
	}

	$userEditor = new \Karolina\User\UserEditor(new \Karolina\User\UserRepository($app['ci']));
	$userEditor->setUser($app['currentUser']);

	$userEditor->updatePassword($request->request->get('new_password'), $request->request->get('old_password'));

	$response['status'] = "success";

	return $app->json($response);

})->before($authRequired);


// Request a password token
$agitator->post('/users/password_resets/', function (Request $request) use ($app) {

	$auth = $app['authInteractor'];
	$email = $request->request->get('email');

	$auth->setEmail($email);
	$auth->resetPassword();

	$responseData['status'] = "success";

	return $app->json($responseData);

});

// Update password with password token
$agitator->post('/users/password_changes/', function (Request $request) use ($app) {

	$userEditor = $app['userEditor'];

	$email = $request->request->get('email');
	$token = $request->request->get('password_reset_token');
	$newPassword = $request->request->get('new_password');

	$userEditor->updatePasswordWithToken($email, $token, $newPassword);

	$responseData['status'] = "success";
	return $app->json($responseData);


});



$agitator->get('/users/{userId}/experiments/preview/{langCode}', function ($userId, $langCode, Request $request) use ($app) {

	if ($userId === "me") {

		if (!$app['currentUser']) {

			throw new Karolina\KarolinaException('You are not logged in', 401, null, 'invalid_login');

		} else {

			$userId = $app['currentUser']->getUserId();

		}

	}

	$interactor = $app['experimentInteractor'];
	$previews = $interactor->getAllPreviewDocumentsByTeamMember($userId, $langCode);
	$response['previews'] = $previews;
	$response['status'] = "success";

	return $app->json($response);


});



return $agitator;
