<?php

require '../bootstrap.php';

use MissionsWorker\Logger;


// Get the raw POST data from the queue (Elastic Beanstalk worker sends it as POST body)
$message = file_get_contents('php://input');


// Pass the message to the logger
$logger = new Logger();
$logger->log($message);

$data = json_decode($message, true);

$emailSender = new MissionsWorker\EmailSender();

// Send the email using the EmailSender class
$results = $emailSender->sendEmail($data);

$logger->log($results);


exit;