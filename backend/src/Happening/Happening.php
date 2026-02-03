<?php

namespace Karolina\Happening;

use Karolina\Exception;
use \Karolina\UUID;

class Happening
{

    private $name;
    private $id;
    private $usersRegistered = array();
    private $applications = array();
    private $schedule = array();
    private $uuid = NULL;
    private $settings;  use \Karolina\Setting\SettingTrait;
    private $active = true;

    private $customLanguageFields; use \Karolina\Language\CustomLanguageTrait;

    public function __construct()
    {
        $this->setSettingsGroup(new \Karolina\Setting\SettingsGroup());

    }

    public function getNotifyEmailsForApplication () {

        if ($this->settingIsSet('application_notify_emails')) {
            return $this->setting('application_notify_emails');
        } else {
            return [];
        }

    }

    public function isPrivate () {

        $config = $this->getConfig();

        if ($config['access'] === 'private') {
            return true;
        }
        return false;
    }

    public function isAcceptingRegistrations () {

        $config = $this->getConfig();

        if ($config['registrations'] === 'open') {
            return true;
        }
        return false;
    }

    public function isAcceptingApplications () {

        $config = $this->getConfig();

        if ($config['registrations'] === 'applications') {
            return true;
        }
        return false;
    }

    public function setConfig ($config) {

        $this->setSetting('config', $config);

        if ($config['name']) {
            $this->setName($config['name']);
        }

    }

    public function getConfig () {

        $defaultConfig['access'] = 'private';
        $defaultConfig['registrations'] = 'open';
        $defaultConfig['notify_on_registrations'] = ["2"];

        $config = [];

        if ($this->settingIsSet('config')) {
            $config = $this->setting('config');
        }

        return array_merge($defaultConfig, $config);
    }

    function getActive () {
        return $this->active;
    }

    function setActive ($active) {

        $this->active = $active;
    }

    function remove () {

        $this->active = false;

    }

    public function setSchedule ($schedule) {

        $this->schedule = $schedule;
    }

    public function getSchedule () {

        return $this->schedule;

    }
    public function setUsersRegistered ($usersRegistered) {

        $this->usersRegistered = $usersRegistered;
    }

    public function setApplications ($applications) {

        $this->applications = $applications;
    }

    public function setUUID ($uuid) {

        if ($uuid == NULL or $uuid == "") {
            $this->uuid = $this->getUUID();
        } else {
            $this->uuid = new UUID($uuid);
        }

    }

    public function getUUID () {

        if ($this->uuid == NULL)  {

            $this->uuid = new UUID();


        }

        return (string) $this->uuid;


    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setName ($name) {

        $this->name = $name;
    }

    public function getName () {

        return (string) $this->name;

    }

    public function updateUserRegistration ($userId, $type) {

        if (isset($this->usersRegistered[$userId])) {
            $this->usersRegistered[$userId]['type'] = $type;
        }

        return true;

    }

    public function registerUser ($userId, $info = false) {

        if (!isset($this->usersRegistered[$userId])) {
            $this->usersRegistered[$userId]['type'] = "participant";

            if ($info) {

                foreach ($info as $key => $question) {

                    if (is_array($question['a'])) {
                        $question['a'] = implode(' /// ' ,$question['a']);
                    }


                    $this->usersRegistered[$userId]['info'][$key]['q'] = (string) $question['q'];
                    $this->usersRegistered[$userId]['info'][$key]['a'] = (string) $question['a'];

                }
            }

        } else {

            throw new Exception('The user is already registered for this happening', 'happening_alreaedy_registered');

        }


    }
    public function apply ($userId, $info = false) {


        $application['user_id'] = (string) $userId;

        if ($info) {

            foreach ($info as $key => $question) {

                $application['info'][$key]['q'] = (string) $question['q'];
                $application['info'][$key]['a'] = (string) $question['a'];

            }
        }

        $this->applications[] = $application;


    }

    public function isUserRegistered ($userId) {

        if (isset($this->usersRegistered[$userId])) {

            return true;

        } else {

            return false;
        }

    }

    public function removeUserRegistration ($userId) {

        unset($this->usersRegistered[$userId]);

        return true;

    }

    public function getRegisteredUsers () {

        return $this->usersRegistered;

    }

    public function getApplications () {

        return $this->applications;

    }


}