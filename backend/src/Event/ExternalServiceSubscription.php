<?php

namespace Karolina\Event;


Class ExternalServiceSubscription implements Subscriber {

    private $platform;
    private $experimentInteractor;

    public function __construct ($platform, $experimentInteractor = null)

    {

        $this->platform = $platform;
        $this->experimentInteractor = $experimentInteractor;


    }

    public function handle ($event) {

        if ($event->getType() == "new_experiment") {

            return $this->newSubmission($event);
        } 

    }

    private function newSubmission ($event) {

        $eventData = $event->getData();        
        $document = $this->experimentInteractor->getDocument($eventData['experiment_id']);
        $experimentResponse = new \Karolina\API\v1\ExperimentResponse($document, 'EN', $this->platform);
        $eventData['preview'] = $experimentResponse->getPreview(false);

        $externalToEventsSubscriptions = $this->platform->getExternalToEventsSubscriptions();

        foreach ($externalToEventsSubscriptions as $subscription) {
            if ($subscription['event_name'] == 'new_experiment') {

                // Currently only Slack is supported
                if ($subscription['service']['type'] == 'slack_webhook') {


                    if (isset($subscription['service']['filters']['stages'])) {


                        if (in_array($eventData['preview']['stage'], $subscription['service']['filters']['stages'])) {


                            return $this->sendToSlackWebhook($eventData, $subscription['service']['data']);
                        }

                    } else {

                        return $this->sendToSlackWebhook($eventData, $subscription['service']['data']);

                    }


                }
            }
        }



    }

    public function sendToSlackWebhook ($eventData, $data) {

        $slackWebhookUrl = $data['url'];
        $slackWebhook = new \Karolina\Event\SlackWebhook($slackWebhookUrl);

        $tagString = "Tags: ";

        foreach ($eventData['preview']['tags'] as $tag) {
            $tagString .= "#" . $tag['label'] . " ";
        }

        $subject = $eventData['submission_title'];
        $stageHandle = $this->platform->getStageHandles()[$eventData['preview']['stage']];

        $sent = $slackWebhook->sendRichMessage([
            [
                "type" => "section",
                "text" => [
                    "type" => "mrkdwn",
                    "text" => "New " . $stageHandle . ":\n*" . $subject . "*"
                ],
                "accessory" => [
                    "type" => "button",
                    "text" => [
                        "type" => "plain_text",
                        "text" => "View submission"
                    ],
                    "url" => $this->platform->getPlatformFrontendUrl().'/submission/'.$eventData['preview']['experiment_id'] ?? "#"
                ]                
            ],
            [
                "type" => "section",
                "text" => [
                    "type" => "mrkdwn",
                    "text" => $tagString
                ]
            ],
            [
                "type" => "section",
                "text" => [
                    "type" => "mrkdwn",
                    "text" => $eventData['preview']['short_description']
                ]
            ]
        ], "New " . $stageHandle);

    }

    public function isSubscribedTo ($aDomainEvent) {

        $externalToEventsSubscriptions = $this->platform->getExternalToEventsSubscriptions();

        $eventsToPush = [];

        foreach ($externalToEventsSubscriptions as $subscription) {
            if (!in_array($subscription['event_name'], $eventsToPush)) {
                $eventsToPush[] = $subscription['event_name'];
            }
        }

        $eventType = $aDomainEvent->getType();

        if (in_array($eventType, $eventsToPush)) {
            return true;
        }

        return false;

    }    


}