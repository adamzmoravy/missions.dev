<?php

namespace Karolina\Event;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use Karolina\Discussion\DiscussionRepository;
use Karolina\Exception;
use Karolina\Experiment\ExperimentRepository;
use Karolina\User\UserRepository;


Class EventRepository
{

    public function getEvents ($limit = 20, $eventTypes = ['new_experiment', 'comment', 'new_user','comment_happening']) {

        $models = \Karolina\Database\Table\Event::take($limit)->orderBy('occurred_on', 'desc')->whereIn('event_type', $eventTypes)->get();
        $events = [];

        foreach ($models as $model) {

            $event = Array();
            $event['event_id'] = $model->event_id;
            $event['occurred_on'] = $model->occurred_on;
            $event['event_type'] = $model->event_type;
            $event['actor_id'] = $model->actor_id;
            $event['data'] = json_decode($model->data, true);
            $events[] = $event;

            try {
                if (false) {
                    if ($event['event_type'] == "new_experiment") {

                        $experimentDoc = $this->getExperimentDocument($event['data']['experiment_id']);
                        $event["experiment"] = $experimentDoc;
                        $events[] = $event;
                    }
                    if ($event['event_type'] == "comment") {
    
                        $experimentDoc = $this->getExperimentDocumentByUUID($event['data']['topic_id']);
                        if (!isset($experimentDoc['settings']['restrictions']['view'])) {
                            $event["comment"] = $this->getComment($event['data']['comment_id']);
                            $event["experiment"] = $experimentDoc;
                            $events[] = $event;
                        }
                    }
    
                    if ($event['event_type'] == "new_user") {
    
                        $event["user"] = $this->getUser($event['data']['user_id']);
                        $events[] = $event;
    
                    }
                }

            } catch (Exception $e) {


            }
            catch (\Exception $e) {


            }

        }

        return $events;

    }


    private function getUser ($id) {


        $userDoc =  (new UserRepository())->getDocumentById($id);

        return $userDoc;

    }


    private function getComment ($id) {

        return (new DiscussionRepository())->getComment($id);

    }


    private function getExperimentDocument ($id) {

        return (new ExperimentRepository())->getDocument($id);

    }

    private function getExperimentDocumentByUUID ($uuid) {

        return (new ExperimentRepository())->getDocumentByUUID  ($uuid);

    }

    public function countEventsSince(int $time, $back = 0, $excludeActorId, $eventTypes = ['new_experiment', 'comment', 'new_user', 'comment_happening'])
    {

        $count = \Karolina\Database\Table\Event::where('occurred_on', '>', $time)->where('occurred_on', '>', $back)->where('actor_id', '!=', $excludeActorId)->whereIn('event_type', $eventTypes)->count();
        return $count;

    }

}