<?php

namespace Karolina\Happening;

Class HappeningMockRepository
{

    private $happeningStore;

    public function createHappening (Happening $happening) {

        $id = "3";

        $this->happeningStore[$id] = $happening;

        return $id;

    }

    public function getById ($id) {

        $happening = $this->happeningStore[$id];

        $happening->setId($id);

        return $happening;
    }


}