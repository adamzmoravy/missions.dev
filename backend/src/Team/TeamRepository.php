<?php

namespace Karolina\Team;


Class TeamRepository  {

    private $userRepository;

    public function __construct (\Karolina\User\UserRepository $userRepository) {

        $this->userRepository = $userRepository;
    }

	public function getTeam ($object_type, $object_id) {

    	$models = \Karolina\Database\Table\Team::where('object_id', $object_id)->where('object_type', $object_type)->get();

    	$team = new Team();

    	foreach ($models as $model) {


			try {

				$user = $this->userRepository->getById($model->user_id);
				$roles = json_decode($model->roles, true);
				$team->addMember($user, $roles);		

			} catch (\Karolina\Exception $e) {

				if ($e->getStatusKey() == 'no_user_found') {
					// User has been deleted. Skip.
					continue;
				} else {
					throw $e;
				}

			}

    	}

    	return $team;

	}

	public function saveTeam (Team $team, $objectType, $objectId) {

    	$teamMembers = $team->getMembers();

    	$memberIds = [];

    	foreach ($teamMembers as $member) {

    		$langFieldModel = \Karolina\Database\Table\Team::updateOrCreate(
    		    [
    		    'object_type' => $objectType, 
                'object_id' => $objectId, 
    		    'user_id' => $member['user']->getId(),
                    'platform' => getenv('platform_key')
    		    ],
    		    [
    		    'roles' => json_encode($member['roles'])
    		    ]);

                $memberIds[$member['user']->getId()] = true;
    	}

    	// Now delete those who are not in the team any more
        $models = \Karolina\Database\Table\Team::where('object_id', $objectId)->where('object_type', $objectType)->get();
        foreach ($models as $model) {
            if (!isset($memberIds[$model->user_id])) {
                $model->delete();
            }
        }

    }

}