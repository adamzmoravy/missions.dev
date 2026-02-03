<?php

namespace MissionsWorker;
use Aws\CloudWatchLogs\CloudWatchLogsClient;

class Logger
{
    public function __construct()
    {
        // Initialization code here
    }

    public function log(string $message): void
    {

        $client = new CloudWatchLogsClient([
            'region' => 'eu-west-1', // Change to your region
            'version' => 'latest',
            'credentials' => [
                'key'    => $_ENV['AWS_ACCESS_KEY_ID'], // Use environment variable for security
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'], // Use environment variable for security
            ],
            
        ]);

        $logGroupName = 'missions-worker';
        $logStreamName = 'emails-sendout';

        // Get the next sequence token
        $result = $client->describeLogStreams([
            'logGroupName' => $logGroupName,
            'logStreamNamePrefix' => $logStreamName,
        ]);
        $logStreams = $result['logStreams'];
        $sequenceToken = isset($logStreams[0]['uploadSequenceToken']) ? $logStreams[0]['uploadSequenceToken'] : null;

        // Prepare the log event
        $event = [
            [
                'timestamp' => round(microtime(true) * 1000),
                'message' => (string) $message,
            ],
        ];

        // Send the log event
        $params = [
            'logGroupName' => $logGroupName,
            'logStreamName' => $logStreamName,
            'logEvents' => $event,
        ];
        if ($sequenceToken) {
            $params['sequenceToken'] = $sequenceToken;
        }

        $client->putLogEvents($params);


    }
}