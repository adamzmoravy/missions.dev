<?php
namespace Karolina\User\Action;

use Karolina\API\v1\CommentsResponse;
use Karolina\Discussion\DiscussionRepository;

Class GetProfile {

	private $currentUser = false;
	private $userRepository;
	private $imgStorageUrl;
	private $response;
	private $user;
    private $platform;

	public function __construct ($currentUser, $userRepository, $imgStorageUrl, $platform = false) {

		$this->currentUser = $currentUser;
		$this->userRepository = $userRepository;
		$this->imgStorageUrl = $imgStorageUrl;
        $this->platform = $platform;
	}


	public function forUserId ($userId, $getContactInfo = false) {

					global $timings;
					$timings->start('forUserId::getUser');

		if ($userId === "me") {

			if (!$this->currentUser) {

				throw new \Karolina\KarolinaException('You are not logged in', 401, null, 'invalid_login');

			} else {

				$user = $this->currentUser;

			}

		} else {

					$timings->start('forUserId::getUserById');

			$userRepository = $this->userRepository;
			$user = $userRepository->getById($userId);

					$timings->end('forUserId::getUserById');

		}

				$timings->end('forUserId::getUser');

		$this->user = $user;

		$imgStorageUrl = $this->imgStorageUrl;
		$currentUser = $this->currentUser;

		foreach ($user->getImageCollectionDocument() as $contentKey => $imageData) {

			$response['profile']['image_collection'][$contentKey]['filename'] = $imageData['filename'];
			$response['profile']['image_collection'][$contentKey]['url'] = $imgStorageUrl."/".$imageData['filename'];

		}

		$response['profile']['happenings'] = [];

					$timings->start('forUserId::getHappenings');

		foreach ($user->getHappeningsRegisteredTo() as $happening) {
            $response['profile']['happenings'][$happening->getId()]['name'] = $happening->getName();
        }
					$timings->end('forUserId::getHappenings');


		$response['profile']['email'] = NULL;
		$response['profile']['phone'] = NULL;
		$response['profile']['document_number'] = NULL;
		$response['profile']['meta'] = NULL;

		if ($currentUser and ($getContactInfo or $currentUser->isAdmin() or $currentUser->isSame($user))) {

			$response['profile']['email'] = $user->getEmail();
			$response['profile']['phone'] = $user->getPhone();
			$response['profile']['document_number'] = $user->getDocumentNumber();
			$response['profile']['company'] = $user->getCompany();
			$meta = $user->getAllProfileMeta();

			if (is_array($meta)) {
				$response['profile']['meta'] = (object) $meta;
			} else {
				$response['profile']['meta'] = $meta;
			}

		}

		$response['profile']['tags_with_type'] = $user->getTagsWithTypeDocument();

		$response['profile']['geographic_location'] = null;

		if ($user->getGeographicLocation() !== null) {

			$response['profile']['geographic_location']['vicinity'] = $user->getGeographicLocation()['vicinity'];
			$response['profile']['geographic_location']['country'] = $user->getGeographicLocation()['country'];
			$response['profile']['geographic_location']['lat'] = $user->getGeographicLocation()['location']['lat'];
			$response['profile']['geographic_location']['lng'] = $user->getGeographicLocation()['location']['lng'];
			$response['profile']['geographic_location']['formatted_address'] = $user->getGeographicLocation()['formatted_address'];

		}
		
		

		$response['profile']['experiments'] = array();

		$response['profile']['links'] = $user->getLinks();
		$response['profile']['skills'] = $user->getSkills();

		$tagResponse = new \Karolina\API\v1\TagsResponse($user->getTagsDocument(), 'EN');

		$response['profile']['tags'] = $tagResponse->get();
		$response['profile']['user_id'] = $user->getId();
		$response['profile']['first_name'] = $user->getFirstName();
		$response['profile']['last_name'] = $user->getLastName();
		$response['profile']['short_description'] = $user->getProfileShortDescription()->getValue();
		$response['profile']['long_description'] = $user->getProfileDescription()->getAsHTML();
		$response['profile']['user_type'] = $user->getUserType();

		$this->response = $response;

		return $this;
	}

	public function withExperiments ($experimentInteractor) {


		$experimentsPreviews = $experimentInteractor->getAllPreviewDocumentsByTeamMember($this->user->getId());
		foreach ($experimentsPreviews as $preview) {

			$this->response['profile']['experiments'][] = (new \Karolina\API\v1\ExperimentResponse($preview, 'EN', $this->platform))->getPreview();

		}

		return $this;

	}


	public function withCommentsStats() {

	    $document = $this->userRepository->getDocumentById($this->user->getId());

        $this->response['profile']['comments_count'] = count($document['comments']);
        $this->response['profile']['comment_types'] = $this->countCommentsByType($document['comments']);

        return $this;
    }


    private function countCommentsByType ($comments) {

        $types = [];
        foreach ($comments as $comment) {

            if (!isset($types[$comment['type']])) {
                $types[$comment['type']] = 0;
            }

            $types[$comment['type']]++;

        }

        return $types;

    }

    public function withAcl () {

        $acl = ['view'];

		if ($this->currentUser) {

            if ($this->currentUser->isSame($this->user)) {
				$acl[] = 'edit';
			}

			if ($this->currentUser->isAdmin()) {
				$acl[] = 'admin';
			}

		}

        $this->response['acl'] = $acl;

        return $this;

	}

	public function getResponse () {

		return $this->response;

	}

}
