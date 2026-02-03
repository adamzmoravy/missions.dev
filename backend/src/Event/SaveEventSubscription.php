<?php

namespace Karolina\Event;


Class SaveEventSubscription implements Subscriber {

    private $actor_id = NULL;

    public function __construct ($user = NULL)

    {
        /* @var $user \Karolina\User\User */
        if ($user) {
            $this->actor_id = $user->getId();
        }

    }

    public function handle ($event) {

        $model = new \Karolina\Database\Table\Event;

        $model->data = json_encode($event->getData());
        $model->event_type = $event->getType();
        $model->occurred_on = $event->occurredOn();
        $model->actor_id = $this->actor_id;

        $model->save();


    }

    public function isSubscribedTo ($aDomainEvent) {

        return true;

    }

}
