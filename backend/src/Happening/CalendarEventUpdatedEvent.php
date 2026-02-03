<?php

namespace Karolina\Happening;

use Karolina\Event\Event;

Class CalendarEventUpdatedEvent implements Event

{

    private $calendarEvent;


    public function __construct (CalendarEvent $calendarEvent)
    {

        $this->calendarEvent = $calendarEvent;

    }

    public function occurredOn()
    {
        return time();
    }

    public function getData()
    {
        $data['calendar_event_id'] = $this->calendarEvent->getId();
        return $data;

    }

    public function getType()
    {

        return "calendar_event_updated";

    }
}