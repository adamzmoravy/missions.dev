<?php

namespace Karolina\Happening;

use Karolina\Exception;
use \Karolina\UUID;
use \Karolina\Keyname;
use \Karolina\Currency;
use Karolina\Image\Image;


class CalendarEvent 
{

    private $id = NULL;
    private $calendars = array();
    private $start;
    private $end;
    private $data = array();
    private $status = 'active';
    private $type = null;
    private $userId = null;
    private $maxParticipants = null;
    private $image = null;

	public function __construct ($start, $end, array $calendars) {

        $this->setTime($start, $end);
        $this->setCalendars($calendars);

    }

    public function isInGroups (array $groupIds) {


        foreach ($groupIds as $groupId) {

            foreach ($this->calendars as $calendar) {

                $groupName = "happening-".$groupId;

                if ($calendar == $groupName) {
                    return true;
                }

            }

        }

        return false;

    }

    public function setMaxParticipants ($maxParticipants) {

        if ($maxParticipants == "" or $maxParticipants == null) {
            $this->maxParticipants = null;
            return true;
        }

        if (is_numeric($maxParticipants)) {

            $this->maxParticipants = (int) $maxParticipants;

        } else {

            throw new Exception("Max participants must be a number");
        }

    }

    public function setImage ($filename) {

        if ($filename == null or $filename == "") {
            $this->image = null;
            return true;
        }

        $image = new Image($filename, 'main');
        $this->image = $image->getFilename();


    }

    public function getImage () {

        return $this->image;

    }

    public function getMaxParticipants () {

        return $this->maxParticipants;

    }    

    public function setUserId ($userId) {

        $this->userId = $userId;
        $this->data['user_id'] = $userId;

    }

    public function getUserId () {
        return $this->userId;

    }

    public function setDescription ($description) {

        $description = substr($description, 0, 2000);
            $this->data['description'] = (string) $description;
    
    }

    public function getDescription () {

        if (isset($this->data['description'])) {
            return $this->data['description'];
        }

        return null;

    }

    public function setName ($name) {

        // limit to 400 characters
        $name = substr($name, 0, 400);

        $this->data['name'] = (string) $name;

    }

    public function getName () {

        if (isset($this->data['name'])) {
            return $this->data['name'];
        }

        return null;

    }

    public function setLocation ($location) {

        // limit to 600 characters
        $location = substr($location, 0, 600);

        $this->data['location'] = $location;

    }

    public function getLocation () {

        if (isset($this->data['location'])) {
            return $this->data['location'];
        }

        return null;

    }

    public function setPrice ($amount, $currency = "ISK") {


        $price['amount'] = (string) $amount;
        $price['currency'] = (string) new Currency($currency);
        $this->data['price'] = $price;


    }

    public function addPlus ($userId, $plus) {

        if ($this->isMaxParticipantsReached()) {
            throw new Exception("Max participants reached");
        }


        if (isset($this->data['attending'][$userId])) {
            $this->data['attending'][$userId]['plus'] += $plus;
        }

    }

    public function getAttendingWithPlusOne () {

        $totalAttending = 0;

        foreach ($this->data['attending'] as $attending) {

            if (isset($attending['plus'])) {
                $totalAttending += $attending['plus'];
            }
            $totalAttending++;

        }

        return $totalAttending;

    }

    public function isMaxParticipantsReached () {

        if ($this->maxParticipants == null) {
            return false;
        }

        if ($this->getAttendingWithPlusOne() >= $this->maxParticipants) {
            return true;
        }

        return false;

    }


    public function addAttending ($userId) {

        if ($this->isMaxParticipantsReached()) {
            throw new Exception("Max participants reached");
        }

        if (!isset($this->data['attending'][$userId])) {
            $this->data['attending'][$userId]['plus'] = 0;
            $this->data['attending'][$userId]['status'] = 'attending';    
        }


    }

    public function removeAttending ($userId) {

        if (isset($this->data['attending'][$userId])) {
            unset($this->data['attending'][$userId]);
        }
    }


    public function setType ($type) {

        $this->type = (string) new Keyname($calendar);

    }

    private function setCalendars ($calendars) {

        foreach ($calendars as $calendar) {

            $this->calendars[] = (string) new Keyname($calendar);

        }

    } 

    public function setTime ($start, $end) {

        $this->start = $start;
        $this->end = $end;

    }

    public function getId() {

        return $this->id;
    }

    public function __get ($property)
    {
        return $this->$property;
    }

    public function __set ($property, $value) {

        $this->$property = $value;

    }

    public function getType () {

        return $this->type;
    }

    public function getAttending() {

        if (isset($this->data['attending'])) {
            return $this->data['attending'];
        }

        return null;

    }

    public function getPrice () {

        if (isset($this->data['price'])) {
            return $this->data['price'];
        }

        return null;
    }

    public function getDocument () {

        $data = array();
        $data['calendars'] = $this->calendars;
        $data['start'] = $this->start;
        $data['end'] = $this->end;
        $data['data'] = $this->data;
        $data['status'] = $this->status;
        $data['type'] = $this->type;
        $data['name'] = $this->getName();
        $data['description'] = $this->getDescription();
        $data['location'] = $this->getLocation();
        $data['user_id'] = $this->getUserId();        
        $data['max_participants'] = $this->getMaxParticipants();
        $data['image'] = $this->getImage();


        return $data;

    }

}