<?php

namespace Karolina\Experiment;


Class Space {

    private $customLanguageFields;
    private $id;
    private $uuid;
    private $key;

    use \Karolina\Language\CustomLanguageTrait;

    public function getId () {
        return $this->id;
    }
    public function getUUID () {
        return $this->uuid;
    }

    public function getKey () {
        return $this->key;
    }

    public function __get ($property)
    {
        return $this->$property;
    }

    public function __set ($property, $value) {

        $this->$property = $value;

    }

}