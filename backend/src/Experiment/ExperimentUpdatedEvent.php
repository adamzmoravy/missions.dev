<?php

namespace Karolina\Experiment;

use Karolina\Event\Event;

Class ExperimentUpdatedEvent implements Event

{

    private $experiment;
    private $updateType;
    private $updateData;

    public function __construct (Experiment $experiment, $updateType, $updateData)
    {

        $this->experiment = $experiment;
        $this->updateType = $updateType;
        $this->updateData = $updateData;

    }

    public function occurredOn()
    {
        return time();
    }

    public function getData()
    {
        $data['experiment_id'] = $this->experiment->getId();
        $data['experiment_uuid'] = $this->experiment->getUUID();
        return $data;

    }

    public function getType()
    {

        return "experiment_updated";

    }
}