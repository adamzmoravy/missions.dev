<?php

namespace Karolina\Experiment;

use Karolina\Event\Event;

Class NewExperimentEvent implements Event

{

    private $experiment;

    public function __construct (Experiment $experiment)
    {

        $this->experiment = $experiment;

    }

    public function occurredOn()
    {
        return time();
    }

    public function getData()
    {
        $data['experiment_id'] = $this->experiment->getId();
        $data['experiment_uuid'] = $this->experiment->getUUID();
        $data['submission_title'] = (string) $this->experiment->getTitle();
        $data['submission_stage'] = (string) $this->experiment->getStage();
        return $data;

    }

    public function getType()
    {

        return "new_experiment";

    }
}