<?php

namespace Karolina;

Class Timings {

    private $timings = [];

    public function start ($name) {

        $this->timings[$name]['start'] = microtime(true);

    }

    public function end ($name) {

        $this->timings[$name]['end'] = microtime(true);
        $this->timings[$name]['difference'] = round($this->getTimingDifference($name), 4);

    }

    public function getTimings () {

        return $this->timings;

    }

    public function getTiming ($name) {

        return $this->timings[$name];

    }

    public function getTimingDifference ($name) {

        return $this->timings[$name]['end'] - $this->timings[$name]['start'];

    }

}