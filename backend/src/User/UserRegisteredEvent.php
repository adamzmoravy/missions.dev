<?php

namespace Karolina\User;
use Karolina\Event\Event;

Class UserRegisteredEvent implements Event {

	private $user;

	public function __construct (User $user) {

		$this->user = $user;
		$this->occurredOn = time();

	}

    public function getData()
    {

        $data['user_id'] = $this->user->getId();
        return $data;

    }

    public function getType()
    {

        return "new_user";

    }

	public function getUserId () {

		return $this->userId;

	}

	public function occurredOn () {

		return $this->occurredOn;
		
	}

}