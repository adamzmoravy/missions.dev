<?php

namespace Karolina\Happening;

use Karolina\Event\Event;

Class HappeningUpdatedEvent implements Event

{

    private $happening;


    public function __construct (Happening $happening)
    {

        $this->happening = $happening;

    }

    public function occurredOn()
    {
        return time();
    }

    public function getData()
    {
        $data['happening_id'] = $this->happening->getId();
        $data['happening_uuid'] = $this->happening->getUUID();
        return $data;

    }

    public function getType()
    {

        return "happening_updated";

    }
}