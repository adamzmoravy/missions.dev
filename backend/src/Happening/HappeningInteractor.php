<?php

namespace Karolina\Happening;

use Karolina\KarolinaException;
use Karolina\User\UserRepository;

Class HappeningInteractor
{


    private $repository;
    private $notificationService;
    private $userRepository;

    public function __construct($happeningRepository, $notificationService, UserRepository $userRepository)
    {

        $this->repository = $happeningRepository;
        $this->notificationService = $notificationService;
        $this->userRepository = $userRepository;

    }


    public function updateCustomLanguage ($happeningId, $customLanguageFields, $langCode) {

        $happening = $this->getHappening($happeningId);

        foreach ($customLanguageFields as $fieldName => $content) {

            $happening->setCustomLanguage($fieldName, $langCode, $content['value'], $content['format']);
        }

        $this->repository->save($happening);

    }

    public function getHappeningData ($happening) {

        $response['happening_id'] = $happening->getId();
        $response['name'] = $happening->getName();
        $response['registrations'] = $happening->getRegisteredUsers();
        $response['schedule'] = $happening->getSchedule();
        $response['uuid'] = $happening->getUUID();
        $response['custom_language'] = $happening->getCustomLanguageFields()->getDocument();
        $response['config'] = $happening->getConfig();

        return $response;

    }

    public function setConfig ($happeningId, $config) {

        $happening = $this->getHappening($happeningId);
        $happening->setConfig($config);
        $this->repository->save($happening);
        return true;

    }

    public function getHappeningDataById ($id) {
        $happening = $this->getHappening($id);

        return $this->getHappeningData($happening);

    }

    public function createHappening ($data) {

        $happening = new Happening();

        $happening->setName($data['name']);
        $id = $this->repository->createHappening($happening);
        return $id;

    }

    public function getHappening ($id) {

        return $this->repository->getById($id);

    }

    public function removeHappening ($id) {

        $happening = $this->repository->getById($id);

        $happening->remove();

        $this->repository->save($happening);


    }

    public function announceRegistration (Happening $happening, $userId, $info) {

        // Send e-mail
        /* @var $notification \Karolina\Notification */
        $notification = $this->notificationService;

        /* @var $user \Karolina\User\User */
        $user = $this->userRepository->getById($userId);

        $data['happening_name'] = $happening->getName();
        $data['user_name'] = $user->getName();
        $data['questions'] = $info;

        $notification->setSubject('New registration for group '.$happening->getName());
        $notification->setBodyFromTemplate('happening_registration', $data);
        $notification->setToAdmin();
        $config = $happening->getConfig();
        $notification->setToUserIds($config['notify_on_registrations']);

        $notification->addToEmailQueue();

    }


    public function announceUnRegistration (Happening $happening, $userId) {

        // Send e-mail
        /* @var $notification \Karolina\Notification */
        $notification = $this->notificationService;

        /* @var $user \Karolina\User\User */
        $user = $this->userRepository->getById($userId);

        $data['happening_name'] = $happening->getName();
        $data['user_name'] = $user->getName();

        $notification->setSubject('Unregistered from group '.$happening->getName());
        $notification->setBodyFromTemplate('happening_unregistration', $data);
        $config = $happening->getConfig();
        $notification->setToUserIds($config['notify_on_registrations']);

        $notification->addToEmailQueue();

    }


    public function announceApplication (Happening $happening, $userId, $info) {

        // Send e-mail
        /* @var $notification \Karolina\Notification */
        $notification = $this->notificationService;


        /* @var $user \Karolina\User\User */
        $user = $this->userRepository->getById($userId);

        $data['happening_name'] = $happening->getName();
        $data['user_name'] = $user->getName();
        $data['questions'] = $info;

        $notification->setSubject('New application for '.$happening->getName());
        $notification->setBodyFromTemplate('happening_application', $data);
        $emails = $happening->getNotifyEmailsForApplication();
        $emails[] = $user->getEmail();

        $notification->setMultipleRecipientsEmail($emails);

        $notification->addToEmailQueue();

    }

    public function setSchedule ($happeningId, $schedule, \Karolina\User\User $user) {

        if ($user->isAdmin()) {

            $happening = $this->getHappening($happeningId);
            $happening->setSchedule($schedule);
            $this->repository->save($happening);

        } else {
            throw new \Karolina\Exception("Current user is not authorized to update schedule", "access_denied", 401);
        }

    }

    public function registerUserToHappening ($userId, $happeningId, $info = false) {


        if ($info) {

            foreach ($info as $key => $question) {

                if (is_array($question['a'])) {
                    $info[$key]['a'] = implode(' /// ' ,$question['a']);
                }

            }
        }


        $happening = $this->getHappening($happeningId);

        $happening->registerUser($userId, $info);

        $this->repository->save($happening);

        $this->announceRegistration($happening, $userId, $info);

        return true;

    }

    public function applyToHappening ($userId, $happeningId, $info = false) {

        $happening = $this->getHappening($happeningId);

        $happening->apply($userId, $info);

        $this->repository->save($happening);

        $this->announceApplication($happening, $userId, $info);

        return true;

    }
    public function updateUserRegistration ($happeningId, $userId, $type) {

        $happening = $this->getHappening($happeningId);

        $happening->updateUserRegistration($userId, $type);

        $this->repository->save($happening);

        return true;

    }

    public function removeUserRegistration ($happeningId, $userId) {

        $happening = $this->getHappening($happeningId);

        $happening->removeUserRegistration($userId);

        $this->announceUnRegistration($happening, $userId);

        $this->repository->save($happening);



    }

    public function getRegisteredUsers ($happeningId) {

        $happening = $this->getHappening($happeningId);

        return $happening->gerRegisteredUsers();

    }

}
