<?php

namespace Karolina\Happening;
use \Karolina\Database\Table\CalendarEvent as Model;
use Carbon\Carbon;

Class CalendarInteractor {

    public function getEventById ($eventId) {

        $model = Model::findOrFail($eventId);
        return $this->modelToEvent($model);

    }

    public function deactivateEvent ($eventId) {

        $model = Model::findOrFail($eventId);
        $model->active = false;
        $model->save();
        \Karolina\Event\Publisher::instance()->publish(new CalendarEventUpdatedEvent($this->modelToEvent($model)));        
    }


    function getEvents ($calendar = null) {

        $models = Model::orderBy('start', 'asc')
        ->whereDate('start', '>=', Carbon::now());

        if ($calendar) {
            $models = $models->whereJsonContains('calendars', $calendar);
        }

        $models = $models->where('active', true);

        $models = $models->get();

        $events = [];
        foreach ($models as $model) {
            $events[] = $this->modelToEvent($model);
        }

        return $events;
        
    }


    function getPastEvents ($calendar = null, $backToDate = null) {

        $models = Model::orderBy('start', 'asc')
        ->whereDate('start', '>', $backToDate);

        if ($calendar) {
            $models = $models->whereJsonContains('calendars', $calendar);
        }

        $models = $models->where('active', true);

        $models = $models->get();

        $events = [];
        foreach ($models as $model) {
            $events[] = $this->modelToEvent($model);
        }

        return $events;
        
    }


    public function saveEvent (CalendarEvent $event) {

        $model = $this->eventToModel($event);
        $model->save();

        \Karolina\Event\Publisher::instance()->publish(new CalendarEventUpdatedEvent($event));        

    }

    private function modelToEvent ($model) {

        $event = new CalendarEvent(
            $model->start,
            $model->end,
            $model->calendars
        );

        $event->__set('id', $model->calendar_event_id);
        $event->__set('data', $model->data);
        $event->__set('userId', $model->user_id);
        $event->__set('type', $model->type);
        $event->__set('status', $model->status);
        $event->__set('maxParticipants', $model->max_participants);
        $event->__set('image', $model->image);

        return $event;
    }

    private function eventToModel (CalendarEvent $event) {

        $model = Model::updateOrCreate(
            ['calendar_event_id' => $event->getId()],
            $event->getDocument()
        );

        $model->user_id = $event->getUserId();

        return $model;
    }


}