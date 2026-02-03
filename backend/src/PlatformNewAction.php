<?php

namespace Karolina;

use Karolina\User\User;
use Karolina\User\UserRepository;

class PlatformNewAction
{

    private $data;
    private $repo;
    private $notificationService;

    public function __construct($data, $notificationService)
    {

        $this->data = $data;
        $this->notificationService = $notificationService;
        $this->repo = new PlatformRepository();

        /************
            ACTIONS
         *************/

        // 1. Create a new platform
        $platform = $this->createNewPlatform();

        // 2. Create the admin user
        $admin = $this->createAdminUser();

        // 3. Create the enviroment
        $envRepo = new EnvironmentRepository();
        $envRepo->newEnvironmentVariable($data['platform_key'], 'platform_key', $data['platform_key']);
        $secretKey = substr(bin2hex(random_bytes(16)), 0, 32);
        $envRepo->newEnvironmentVariable($data['platform_key'], 'platform_secret_key', $secretKey);

        // 4. Create the host
        $this->newHost();

        // 5. Notify creation
        $this->notifyOfCreation($data['platform_key']);


    }

    private function notifyOfCreation ($platform) {

        $notification = $this->notificationService;
    
        $data['body'] = "A new platform has been created: ".$platform;

        $notification->setSubject('New platform registered '.$platform);
        $notification->setBodyFromTemplate('generic', $data);
        $notification->setToAdmin();
        
        $notification->send();

    }

    private function newHost () {

        $data = $this->data;
        $envRepo = new EnvironmentRepository();
        $envRepo->newHost($data['platform_key'].'.missions.dev', $data['platform_key']);

    }

    private function createNewPlatform () {

        $newPlatform = $this->repo->newPlatform($this->data['platform_key']);

        $newPlatform->setSiteAccess('public');

        return $newPlatform;

    }

    private function createAdminUser () {
        
        $data = $this->data;
        $newAdminUser = new User(null, $data['admin']['email'], $data['admin']['first_name'], $data['admin']['last_name']);
        $newAdminUser->setNewPassword($data['admin']['password']);
        $newAdminUser->makeAdmin();

        $userRepo = new UserRepository();
        $response = $userRepo->createNew($newAdminUser, $this->data['platform_key']);
        $savedUser = $response[1];
        return $savedUser;

    }

}