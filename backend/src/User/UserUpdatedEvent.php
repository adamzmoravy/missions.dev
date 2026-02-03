<?php

namespace Karolina\User;

use Karolina\Event\Event;

Class UserUpdatedEvent implements Event

{

    private $user;

    public function __construct (User $user)
    {

        $this->user = $user;


    }

    public function occurredOn()
    {
        return time();
    }

    public function getData()
    {
        $data['user_id'] = $this->user->getId();
        return $data;

    }

    public function getType()
    {

        return "user_updated";

    }
}