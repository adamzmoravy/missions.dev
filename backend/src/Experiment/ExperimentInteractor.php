<?php

namespace Karolina\Experiment;

use Karolina\Exception;
use Karolina\User\User;

Class ExperimentInteractor {

	private $experiment;
	/** @var  ExperimentRepository */
	private $repository;
	private $currentUser = false;

	public function __construct ($experimentRepository) {

		$this->repository = $experimentRepository;

	}



	function setMeta ($id, $key, $value) {

		return $this->repository->setMeta($id, $key, $value);


	}

	public function getStats ($showIn = false) {

        return $this->repository->getExperimentStats($showIn);
    }

	public function getRepository () {

		return $this->repository;

	}

	public function setCurrentUser (\Karolina\User\User $user) {

		$this->currentUser = $user;

	}

	public function getExperiment () {

		return $this->experiment;

	}

	public function setExperimentById ($id) {

		$this->experiment = $this->repository->getById($id);
		return $this;

	}

	public function getDocumentAsUser ($experimentId, $langCode = false, $user) {

		$document = $this->repository->getDocument($experimentId, $langCode);

		if (isset($document['settings']['restrictions']['view'])) {


		if ($user) {
                $experiment = $this->setExperimentById($experimentId)->getExperiment();
                if ($this->isExperimentAccessibleToUser($experiment, $user)) {
                    return $document;
                }
            }

		    throw new Exception('View access to this experiment is restricted', 'access_denied');

        }

		return $document;

	}

	private function isExperimentAccessibleToUser (Experiment $experiment, User $user) {

	    $restrictions = $experiment->getRestrictions();

        if ($user and $experiment->isInTeamOrIsAdmin($user)) {

            return true;

        } else {

            if (isset($restrictions['view']['happening_registered'])) {
                // Is user in happening?
                foreach ($restrictions['view']['happening_registered'] as $happeningId) {

                    return $user->isHappeningRegisteredTo($happeningId);

                }

            }
        }

        return false;

    }

	public function getDocument ($experimentId, $langCode = false) {

		$document = $this->repository->getDocument($experimentId, $langCode);
		return $document;

	}

	public function addLike () {

		$this->experiment->addLike($this->currentUser);
		$this->repository->save($this->experiment);

	}


	public function removeLike () {

		$this->experiment->removeLike($this->currentUser);
		$this->repository->save($this->experiment);

	}



	public function addHelper () {

		$this->experiment->addHelper($this->currentUser);
		$this->repository->save($this->experiment);

	}


	public function removeHelper () {

		$this->experiment->removeHelper($this->currentUser);
		$this->repository->save($this->experiment);

	}



    public function addUpdatesFollower () {

        $this->experiment->addUpdatesFollower($this->currentUser);
        $this->repository->save($this->experiment);

    }


    public function removeUpdatesFollower () {

        $this->experiment->removeUpdatesFollower($this->currentUser);
        $this->repository->save($this->experiment);

    }




    public function addExperimentRelationship (Experiment $experiment) {

		$this->experiment->addExperimentRelationship($experiment);
		$this->repository->save($this->experiment);

	}

    public function removeExperimentRelationship (Experiment $experiment) {

		$this->experiment->removeExperimentRelationship($experiment);
		$this->repository->save($this->experiment);

	}



	public function getAllPreviewDocumentsByTeamMember ($userId, $langCode = 'EN') {

		$filterArguments['team_member_id'] = $userId;

		return $this->getPreviewDocuments($langCode, ['team_member_id' => $userId]);

	}


	public function getPreviewDocuments ($langCode, $filterArguments = array()) {

		$documents = $this->repository->getDocuments($filterArguments);

		return $documents;
	}

	private function setIfExists ($toSet, $fallback = '') {


	}

	public function massUpdateSetting ($ids, $setting, $value) {

		$this->repository->massUpdateSetting($ids, $setting, $value);

	}

	public function shareExperiment (array $shareWithUserIds, $message, $userWhoShares, $notificationService, $platform) {




		$document = $this->repository->getDocument($this->experiment->getId());

		$submissionsPreview = (new \Karolina\API\v1\ExperimentResponse($document, 'en', $platform))->getPreview(false);

		$profilePreview['name'] = $userWhoShares->getName();
		$profilePreview['user_id'] = $userWhoShares->getId();
		$profilePreview['image'] = $userWhoShares->getProfileImage();


		$notificationService->setToUserIds($shareWithUserIds);


		$data = array(
			'message' => $message,
			'submission' => $submissionsPreview,
			'user_who_shares' => $profilePreview
		);

		$notificationService->setBodyFromTemplate('submission_shared', $data);

		$subject = $userWhoShares->getFirstName() . ' shared "' . $submissionsPreview['title'] . '" with you';

		$notificationService->setSubject($subject);
		$notificationService->send();


	}

}
