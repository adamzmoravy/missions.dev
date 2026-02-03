<?php

namespace Karolina\Event;

interface Event

{

	public function occurredOn();
    public function getData ();
    public function getType ();


 }