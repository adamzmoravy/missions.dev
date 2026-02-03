<?php

// COMPOSER
require_once 'vendor/autoload.php';

$stopwatch = new Symfony\Component\Stopwatch\Stopwatch();
$ciExecution = $stopwatch->start('ci_execution');
global $eventTimes;


// Loads more env variables from environments database (for multi platform instances)
if (getenv('load_variables_by_hostname') == "yes") {

    $dotenv = new Dotenv\Dotenv(__DIR__);
	$dotenv->safeLoad();
    $environment = new \Karolina\Environment($dotenv);
    $environment->loadEnvVariablesByHostname($_SERVER['HTTP_HOST']);

} else {
    $dotenv = new Dotenv\Dotenv(__DIR__ . '/../', 'config.env');
    $dotenv->load();
    $environment = new \Karolina\Environment($dotenv);
}

$environment->setLoadedEnvVariables();



//$environment->dotenv->required('timezone')->notEmpty();
$environment->dotenv->required('platform_config')->notEmpty();
$environment->dotenv->required('environment')->allowedValues(['production', 'development']);
$environment->dotenv->required('protocol')->allowedValues(['http', 'https']);
$environment->dotenv->required('platform_secret_key')->notEmpty();
$environment->dotenv->required('database_name')->notEmpty();
$environment->dotenv->required('database_hostname')->notEmpty();
$environment->dotenv->required('database_user')->notEmpty();
$environment->dotenv->required('database_pass');
$environment->dotenv->required('allow_api_access')->allowedValues(['yes', 'no']);


date_default_timezone_set(getenv('timezone'));

if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
	$protocol_array = explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO']);
	$current_protocol = $protocol_array[0];
} else {
	if (isset($_SERVER['HTTPS'])) {
		$current_protocol = 'https';
	} else {
		$current_protocol = 'http';
	}
}


// Forward us to a secure protocol, if we are not on one
if (getenv('protocol') == 'https' and $current_protocol != 'https') {

	header('Location: https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
	exit;

}

define('ENVIRONMENT',  getenv('environment'));
define('DATABASE_HOSTNAME',  getenv('database_hostname'));
define('DATABASE_PASSWORD',  getenv('database_password'));
define('DATABASE_NAME',  getenv('database_name'));
define('ENCRYPTION_KEY',  getenv('encryption_key'));
define('PAYMENT_GATEWAY',  getenv('payment_gateway'));


/*********************************************
	Database
*********************************************/

$database = new \Karolina\Database\DatabaseConnector();
$database->addConnection(getenv('database_name'));
$database->addConnection('environments', 'environments');
$database->makeGlobal();
	
include 'silex/App.php';


/* End of file index.php */
/* Location: ./index.php */