<?php

namespace Karolina\API\v1;
use Karolina\Experiment\Space;

Class SpaceResponse extends Response
{

    private $space;

    public function __construct(Space $space) {

        $this->space = $space;

    }

    public function get () {


        $response['language'] = $this->space->getCustomLanguageFields()->getDocument();
        $response['title'] = (string) $this->space->getCustomLanguage('title');
        $response['id'] = $this->space->getId();
        $response['uuid'] = $this->space->getUUID();
        $response['key'] = $this->space->getKey();
        return $response;

    }

}

