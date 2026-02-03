<?php

namespace Karolina\API\v1;

Class CalendarEventResponse extends Response {

    private $event;


    public function __construct ($event) {

        $this->event = $event;

    }

    public function get () {

        $doc = $this->event->getDocument();
        $r['calendar_event_id'] = (string) $this->event->getId();
        $r['start'] = (string) $doc['start'];
        $r['end'] = (string) $doc['end'];
        $r['calendars'] = (array) $doc['calendars'];
        $r['price'] = $this->event->getPrice();
        $r['attending'] = (object) $this->event->getAttending();
        $r['type'] = (string) $this->event->getType();
        $r['name'] = (string) $doc['name'];
        $r['description'] = (string) $doc['description'];
        $r['location'] = (string) $doc['location'];
        $r['user_id'] = (string) $this->event->getUserId();
        $r['event_id'] = (string) $this->event->getId();
        $r['max_participants'] = (int) $this->event->getMaxParticipants();
        $r['image'] = (string) $this->event->getImage();


        return $r;

    }

}