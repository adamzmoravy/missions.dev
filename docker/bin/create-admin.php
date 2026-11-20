<?php
/**
 * Create an administrator for the local platform (same steps as PlatformNewAction::createAdminUser).
 *
 *   make admin EMAIL=you@example.com PASSWORD=secret123
 *   docker compose exec app php docker/bin/create-admin.php <email> <password> [first] [last]
 */
if ($argc < 3) {
    fwrite(STDERR, "Usage: php docker/bin/create-admin.php <email> <password> [first_name] [last_name]\n");
    exit(1);
}
list(, $email, $password) = $argv;
$first = isset($argv[3]) ? $argv[3] : 'Admin';
$last  = isset($argv[4]) ? $argv[4] : 'User';

$root = realpath(__DIR__ . '/../..');
require $root . '/backend/vendor/autoload.php';

// CLI has no web server context; the repository code reads these.
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST'] = 'localhost';

// Repository code expects the profiling helper that silex/bootstrapme.php normally creates.
$GLOBALS['timings'] = new \Karolina\Timings();

$dotenv = new Dotenv\Dotenv($root, 'config.env');
$dotenv->load();

$database = new \Karolina\Database\DatabaseConnector();
$database->addConnection(getenv('database_name'));
$database->addConnection('environments', 'environments');
$database->makeGlobal();

$user = new \Karolina\User\User(null, $email, $first, $last);
$user->setNewPassword($password);
$user->makeAdmin();

$repo = new \Karolina\User\UserRepository();
list(, $saved) = $repo->createNew($user, getenv('platform_key'));

echo "Created admin #{$saved->getId()} <{$email}> on platform '" . getenv('platform_key') . "'\n";
