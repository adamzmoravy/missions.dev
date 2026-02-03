<?php

namespace Karolina\Event;


Class PushToFrontEndSubscription implements Subscriber {

    private $pusherData;
    private $platformKey;

    public function __construct ($pusherData = NULL, $platformKey)

    {

        $this->pusherData = $pusherData;
        $this->platformKey = $platformKey;

    }

    public function handle ($event) {

        $options = array(
            'cluster' => 'eu',
            'useTLS' => true
        );
        $pusher = new \Pusher\Pusher(
            $this->pusherData['pusher_api_key'],
            $this->pusherData['pusher_secret'],
            $this->pusherData['pusher_app_id'],
            $options
        );

        $data['message'] = 'Something has moved around!';
        $data['event'] = $event->getData();

        $pusher->trigger($this->platformKey, $event->getType(), $data);

    }

    public function isSubscribedTo ($aDomainEvent) {

        $eventsToPush = ["experiment_updated", "new_experiment", "new_user", "user_updated", "comment", "comment_happening", "happening_updated", "calendar_event_updated"];

        $eventType = $aDomainEvent->getType();

        if (in_array($eventType, $eventsToPush)) {
            return true;
        }

        return false;

    }

}
