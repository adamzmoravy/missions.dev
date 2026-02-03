<?php



use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Silex\Provider;
use Symfony\Component\Translation\Loader\YamlFileLoader;


$timings = new Karolina\Timings();
global $timings;

$timings->start('app');

$timings->start('bootstrap');

/*********************************************
	LANGUAGE
*********************************************/

if (!defined('LANGUAGE')) {
	define('LANGUAGE', 'EN');
}

/*********************************************
	Current, logged in user
*********************************************/

$app['currentUser'] = false;


/*********************************************
	Platform
*********************************************/

$platformRepository = new \Karolina\PlatformRepository();
$platform =  $platformRepository->getPlatformByKey(getenv('platform_key'));
$settingRepository = new Karolina\Setting\SettingRepository();
$settings = $settingRepository->getAll();
$platform->setSettingsGroup($settings);

$app['platform'] = $platform;


/*********************************************
	JSON requests
*********************************************/

$app->before(function (Request $request) {
	header('Content-Type: application/json; charset=UTF-8');


    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }

});


/*********************************************
	Logged in user
*********************************************/


$app->before(function (Request $request, $app) {

	global $timings;

	$timings->start('bootstrap_logged_in_user_get');

	$userRepository = new \Karolina\User\UserRepository();

	$app['currentUser'] = false;

	try {

		if ($authHeader = $request->headers->get('Authorization')) {

			$app['currentUser'] = $userRepository->getWithToken($authHeader);
	
		}	

	} catch (Exception $e) {

		$response['message'] = $e->getMessage();
		$response['status'] = 'invalid_token';
		$code = 401;

		return $app->json($response, $code);

	}

	$timings->end('bootstrap_logged_in_user_get');


});



/*********************************************
	Debug
*********************************************/

if (getenv('environment') == 'development')  {

	$app['debug'] = true;

} else {

	$app['debug'] = false;

}


/*********************************************
	Translation
*********************************************/

$app->register(new Silex\Provider\TranslationServiceProvider());

$app['translator']->setLocale(strtolower(LANGUAGE));


/*********************************************
	Templates
*********************************************/


$app->register(new Silex\Provider\TwigServiceProvider());

$app->extend('twig', function($twig, $app) {
	global $timings;

	$twig->addGlobal('base_url', $app['platform']->getPlatformFrontendUrl());
	$twig->addGlobal('site_title', $app['platform']->getSiteTitle());

	$configRaw = $app['platform']->getConfigRaw();
	$twig->addGlobal('ui', $configRaw['platform']['ui']);

	$timings->start('bootstrap_templates');

	$loaders = array();
	$default = new Twig_Loader_Filesystem(__DIR__.'/../templates/default/twig');
	$customTemplatesDir = __DIR__.'/../templates/platforms/'.$app['platform']->conf('key').'/twig/';

	if (file_exists($customTemplatesDir)) {
		$custom = new Twig_Loader_Filesystem($customTemplatesDir);
		$loaders = array($custom, $default);
	} else {

		$loaders = array($default);

	}

	$loader = new Twig_Loader_Chain($loaders);
	$twig->setLoader($loader);

	$timings->end('bootstrap_templates');

	return $twig;

});



/*********************************************
	AWS service
*********************************************/


$app['s3'] = function ($app) {

	$aws = new Aws\Sdk([
	    'region'   => $app['platform']->conf('aws_region'),
	    'version'  => 'latest'
	]);
	return $aws->createS3();

};


/*********************************************
	Interactors
*********************************************/


// User
$app['userRepository'] = new \Karolina\User\UserRepository($app['ci']);

$app['userInteractor'] = function ($app) {

    $interactor = new \Karolina\User\UserInteractor($app['userRepository']);
    return $interactor;

};

// Notifications
$app['notificationService'] = function ($app) {

	$notification = new Karolina\Notification();
	$notification->setTemplateEngine($app['twig']);
	$notification->setUserInteractor($app['userInteractor']);

	return $notification;

};



// Experiment
$app['experimentInteractor'] = function ($app) {

	return new Karolina\Experiment\ExperimentInteractor(
		new Karolina\Experiment\ExperimentRepository($app['ci'])
	);

};



// Happening
$app['happeningInteractor'] = function ($app) {

    return new Karolina\Happening\HappeningInteractor(
        new Karolina\Happening\HappeningRepository(),
        $app['notificationService'],
        new \Karolina\User\UserRepository()
    );

};

$app['experimentEditor'] = function ($app) {

	$editor = new Karolina\Experiment\ExperimentEditor(
		$app['currentUser'],
		$app['experimentInteractor'],
		new Karolina\Experiment\ExperimentRepository($app['ci']),
        $app['platform']
		);

	return $editor;

};


$app['userEditor'] = function ($app) {

	$editor = new \Karolina\User\UserEditor($app['userRepository']);
	$editor->setPlatform($app['platform']);
	return $editor;

};

$app['authInteractor'] = function ($app) {

	$authInteractor = new Karolina\User\AuthInteractor();
	$authInteractor->setNotificationService($app['notificationService']);
	$authInteractor->setUserRepository(new Karolina\User\UserRepository());
	$authInteractor->setPlatform($app['platform']);


	return $authInteractor;

};


/*********************************************
	AFTER MIDDLEWARE
*********************************************/


$app->after(function (Request $request, Response $response, $app) {

});



/*********************************************
	AUTHENTICATION MIDDLEWARE
*********************************************/

$adminOnly = function (Request $request, Silex\Application $app) {
    /** @var \Karolina\User\User $currentUser */
    $currentUser = $app['currentUser'];
	if ($currentUser and $currentUser->isAdmin()) {
        // We are admin, it's ok
	} else {
        $response['status'] = "admin_only";
        return $app->json($response, 401);

    }
};


$authRequired = function (Request $request, Silex\Application $app) {

	if (!$app['currentUser']) {

		$response['status'] = "authorization_required";
		$response['message'] = "You must be logged in to do this.";
		return $app->json($response, 401);

	}
};



/*********************************************
EVENTS
 *********************************************/

// To be decided: How do we handle subscriptions to events?
// Should we contain them in the interactors and then call their methods here?

$app->before(function (Request $request) use ($app) {

    $app['platform']->subscribeToEvents( new \Karolina\Event\SaveEventSubscription($app['currentUser']));

    $pusherData['pusher_api_key'] = getenv('pusher_api_key');
    $pusherData['pusher_secret'] = getenv('pusher_secret');
    $pusherData['pusher_app_id'] = getenv('pusher_app_id');
	
    $app['platform']->subscribeToEvents( new \Karolina\Event\PushToFrontEndSubscription($pusherData, getenv('platform_key')));
	$app['platform']->subscribeToEvents( new \Karolina\Event\NotifyUsersSubscription($app['platform'], $app['notificationService'], $app['experimentInteractor']) );
	$app['platform']->subscribeToEvents( new \Karolina\Event\ExternalServiceSubscription($app['platform'], $app['experimentInteractor']) );


});

/*********************************************
	CATCH ERRORS
*********************************************/

$app->error(function (\Exception $e, $code, $status = "error") use ($app) {


    if ($e instanceof Karolina\KarolinaException) {

		$response['status']= $e->getStatusKey();

		if ($e->getCode()) {

			$code = $e->getCode();

		}
		$response['message'] = $e->getMessage();

    } else if ($e instanceof Karolina\Exception)  {


		$response['status']= $e->getStatusKey();
		$response['exception_type']= "Karolina\Exception";


		$code = $e->getHttpCode();
		$response['message'] = $e->getMessage();

    }


     else {

		$response['status']= "error";
	    if ($app['debug']) {
			$response['message'] = $e->getMessage();

	    } else {
			$response['message'] = "There has been an error. Please contact system admin if this continues.";
	    }

		
	}


	return $app->json($response, $code);


});


$timings->end('bootstrap');


/*********************************************
	HTTP ROUTES
*********************************************/

// Agitator API
$app->mount('/', include __DIR__ . '/Routes.php');
$app->mount('/', include __DIR__ . '/AccountsRoutes.php');
$app->mount('/', include __DIR__ . '/ResourcesRoutes.php');
$app->mount('/', include __DIR__ . '/ExperimentsRoutes.php');
$app->mount('/', include __DIR__ . '/UsersRoutes.php');
$app->mount('/', include __DIR__ . '/TopicsRoutes.php');
$app->mount('/', include __DIR__ . '/HappeningsRoutes.php');


if ($app['platform']->conf('is_sandbox') === 'yes' or $app['debug'] == true) {

	if (file_exists(__DIR__.'/sandbox/SandboxRoutes.php')) {
		// Allow testing, unsafe and potentially destructive actions
		$app->mount('/sandbox', include __DIR__.'/sandbox/SandboxRoutes.php');

	}


}

//................... let's go:
