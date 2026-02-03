<?php

namespace Karolina\Event;


Class NotifyUsersSubscription implements Subscriber {

    private $platform;
    private $notificationService;
    private $experimentInteractor;

    public function __construct ($platform, $notificationService, $experimentInteractor = null)

    {

        $this->platform = $platform;
        $this->notificationService = $notificationService;
        $this->experimentInteractor = $experimentInteractor;


    }

    public function handle ($event) {

        if ($event->getType() == "new_experiment") {

            return $this->newSubmission($event);
        } 


        $notificationService = $this->notificationService;
        $notificationService->setSubject("A new event has occurred: " . $event->getType());
        $notificationService->setBodyFromPlainText("An event of type " . $event->getType() . " has occurred with data: " . print_r($event->getData(), true));
        $notificationService->send();
        return true;

    }

    private function newSubmission ($event) {

        $usersToEventsSubscriptions = $this->platform->getUsersToEventsSubscriptions();

        $userIds = [];
        foreach ($usersToEventsSubscriptions as $subscription) {
            if ($subscription['event_name'] == $event->getType()) {
                $userIds = array_merge($userIds, $subscription['users']);
            }
        }

        $eventData = $event->getData();

        
        $document = $this->experimentInteractor->getDocument($eventData['experiment_id']);

        $experimentResponse = new \Karolina\API\v1\ExperimentResponse($document, 'EN', $this->platform);
        $eventData['preview'] = $experimentResponse->getPreview(false);

        $notificationService = $this->notificationService;
        $notificationService->setToUserIds($userIds);

        $tagString = "";

        foreach ($eventData['preview']['tags'] as $tag) {
            $tagString .= "#" . $tag['label'] . " ";
        }

        if (strlen($tagString) > 0) {
            $tagString = $tagString." | ";
        } else {
            $tagString = "New submission";
        }

        $subject = $tagString.' '.$eventData['submission_title'];

        $notificationService->setSubject($subject);
        $notificationService->setBodyFromTemplate('new_submission_published',$eventData);
        $notificationService->send();

        return true;

    }

    public function isSubscribedTo ($aDomainEvent) {

        $usersToEventsSubscriptions = $this->platform->getUsersToEventsSubscriptions();

        $eventsToPush = [];

        foreach ($usersToEventsSubscriptions as $subscription) {
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
