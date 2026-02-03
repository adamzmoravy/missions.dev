<?php

namespace Karolina\Event;
use Karolina\Event\Event;

Class EveryMinuteEvent implements Event {

	// in seconds
	const DAY = 86400; 
	const HOUR = 3600;

	public function __construct () {

		$this->occurredOn = time();

	}

	public function getData () {

	    $data['time'] = $this->occurredOn;
	    return $data;

    }

    public function getType () {

	    return "every_minute";

    }

	public function occurredOn () {

		return $this->occurredOn;
		
	}

}