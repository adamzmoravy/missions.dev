<?php

namespace Karolina\Experiment;

use Karolina\User\User;
use Karolina\Event\Publisher;

Class ExperimentEditor {

	private $currentUser;
	private $interactor;
	private $repository;
	private $platform;

	public function __construct ($currentUser, $interactor, $repository, $platform) {

		$this->currentUser = $currentUser;
		$this->interactor = $interactor;
		$this->repository = $interactor->getRepository();
		$this->platform = $platform;

	}

	public function createFromComment ($comment, $stage, $creator, $discussionRepository) {

		$newExperiment = new Experiment($this->platform->getCurrencyISO());
		$newExperiment->addCreator($creator);
		$newExperiment->setStage($stage);
		$experimentId = (string) $this->repository->create($newExperiment);
		$this->setExperimentById($experimentId);

		$language['title']['value'] = "New submission from comment";
		$language['short_description']['value'] = $comment->getPlainTextValue();

		$this->updateDefaultLanguage($language, 'en');

		$this->publishExperiment();

		// Now we need to add all the replies as commments to the submisson

		$commentReplies = $comment->getReplies();

		$response = [];

		$comment->setSingleSubmission($experimentId);
		$discussionRepository->saveComment($comment);

		foreach ($commentReplies as $reply) {

			$response['topics'][] = $this->interactor->getExperiment()->getTopicId();
			$reply->setTopicId($this->interactor->getExperiment()->getTopicId());
			$reply->setParentId(null);
			$response['documents'][] = $reply->getDocument();
			$discussionRepository->saveComment($reply);

		}

		return $response;

	}

	public function addFileAttachment ($file, $name, $s3) {

		$experiment = $this->interactor->getExperiment();

		if ($experiment->canEditContent($this->currentUser)) {

			$attachmentFileRepository = new \Karolina\Content\AttachmentFileRepository($s3);
			$destinationFolder = getenv('platform_key').'/submission/'.$experiment->getId().'/attachments';
		
			$filename = $attachmentFileRepository->storeAttachmentFile($file, $destinationFolder);

			$data['filename'] = $filename;

			$experiment->addAttachment('file', $data, $name);
			$this->repository->save($experiment);

		} else {

			throw new \Karolina\Exception("Current user is not authorized to add attachments", "access_denied", 401);
		}

	}

	public function removeAttachment ($attachmentId) {

		$experiment = $this->interactor->getExperiment();

		if ($experiment->canEditContent($this->currentUser)) {

			$experiment->removeAttachment($attachmentId);
			$this->repository->save($experiment);

		} else {

			throw new \Karolina\Exception("Current user is not authorized to remove attachments", "access_denied", 401);
		}

	}

	public function addTeamMember (User $newTeamMemberUser) {

        $experiment = $this->interactor->getExperiment();

        if ($experiment->canEditSettings($this->currentUser) OR $experiment->isJoinable()) {

            $experiment->addTeamMember($newTeamMemberUser);

            $this->repository->save($experiment);

        } else {

            throw new \Karolina\Exception("Current user is not authorized to update settings", "access_denied", 401);

        }

    }

    public function removeTeamMember (User $teamMemberToRemove) {

        /* @var $experiment Experiment */
        $experiment = $this->interactor->getExperiment();

        if ($experiment->canEditSettings($this->currentUser)) {

            $experiment->removeTeamMember($teamMemberToRemove);

            $this->repository->save($experiment);

        } else {

            throw new \Karolina\Exception("Current user is not authorized to update settings", "access_denied", 401);

        }

    }

	public function createNew ($stage = false, $visibility = null) {

		$newExperiment = new Experiment($this->platform->getCurrencyISO());
		$newExperiment->addCreator($this->currentUser);

		if ($stage) {
			$newExperiment->setStage($stage);

		}

		if ($visibility) {
			$newExperiment->setVisibility($visibility);
		}

		$experimentId = (string) $this->repository->create($newExperiment);
        return $experimentId;

	}

	public function setExperimentById ($experimentId) {

		$this->interactor->setExperimentById($experimentId);

	}

    public function publishExperiment () {

        $experiment = $this->interactor->getExperiment();

        if ($experiment->canEditSettings($this->currentUser)) {

            $experiment->setPlaceToShow('index', TRUE);
            $experiment->setPlaceToShow('browse_exp', TRUE);

            $this->repository->save($experiment);
            Publisher::instance()->publish(new NewExperimentEvent($experiment));

        } else {

			throw new \Karolina\Exception("Current user is not authorized to update settings", "access_denied", 401);

		}
    }

	public function disableExperiment () {
		
		$experiment = $this->interactor->getExperiment();

		if ($experiment->canEditAbility($this->currentUser)) {

			$experiment->disable();
			$this->repository->save($experiment);

		} else {

			throw new \Karolina\Exception("Current user is not authorized to update settings", "access_denied", 401);

		}

	}

    public function unpublishExperiment (){

	    /* @var $experiment \Karolina\Experiment\Experiment */
        $experiment = $this->interactor->getExperiment();

        if ($experiment->canEditSettings($this->currentUser)) {

            $experiment->setPlaceToShow('index', FALSE);
            $experiment->setPlaceToShow('browse_exp', FALSE);


            $this->repository->save($experiment);

        } else {

			throw new \Karolina\Exception("Current user is not authorized to update settings", "access_denied", 401);

		}
    }

	public function updateSettings ($newSettings) {

	    /* @var $experiment \Karolina\Experiment\Experiment */
		$experiment = $this->interactor->getExperiment();

		// Can we edit this?
		if ($experiment->canEditSettings($this->currentUser)) {

            $updateType = "settings";
            $updateData = $newSettings;

			if (isset($newSettings['status'])) {
				
                $experiment->setStatus($newSettings['status']);

            }

            if (isset($newSettings['restrictions'])) {

                $experiment->setRestrictions($newSettings['restrictions']);

            }

			if (array_key_exists('geographic_location', $newSettings)) {

				$experiment->setGeographicLocation($newSettings['geographic_location']);

			}

			if (isset($newSettings['stage'])) {

				$experiment->setStage($newSettings['stage']);

			}

			if (isset($newSettings['disabled']) and $experiment->canEditAbility($this->currentUser)) {

				if ($newSettings['disabled'] == true) {
					$experiment->disable();
				} else {
					$experiment->enable();
				}

			}

			if (isset($newSettings['show_in']) and $experiment->canEditWhereToShow($this->currentUser)) {

				foreach ($newSettings['show_in'] as $place => $set) {


					$experiment->setPlaceToShow($place, $set);

				}
			}

            if (isset($newSettings['show_in_placement'])) {

                foreach ($newSettings['show_in_placement'] as $place => $placement) {

                    $experiment->setPlaceToShowPlacement($place, $placement);

                    $updateType = "placement";

                }
            }


            $this->repository->save($experiment, $updateType, $updateData);

		} else {

			throw new \Karolina\Exception("Current user is not authorized to update settings", "access_denied", 401);

		}

	}

	public function addToPhotoStream ($filename, $imageRecordRepository) {


		$experiment = $this->interactor->getExperiment();

		if ($experiment->canEditContent($this->currentUser)) {

			$image = new \Karolina\Image\Image($filename, 'submission-photo-stream-'.$experiment->getId());

			$imageRecordRepository->saveImageRecord(
				$image,
				$this->currentUser->getId(),
			);
	

		} else {

			throw new \Karolina\Exception("Current user is not authorized to update settings", "access_denied", 401);
		}

	}


	public function updateImages ($imageCollectionData) {

		$experiment = $this->interactor->getExperiment();

		if ($experiment->canEditContent($this->currentUser)) {

			$experiment->setImageCollectionFromDocument($imageCollectionData);
			$this->repository->save($experiment);

		} else {

			throw new \Karolina\Exception("Current user is not authorized to update images", "access_denied", 401);
		}

	}



	public function moveToNextStage () {

		$experiment = $this->interactor->getExperiment();

		if ($this->currentUser and $experiment->canEditSettings($this->currentUser)) {

			$experiment->moveToNextStage();
			$this->repository->save($experiment);

		} else {

			throw new \Karolina\Exception("Current user is not authorized to change the stage", "access_denied", 401);
		}

	}

	public function moveToPreviousStage () {

		$experiment = $this->interactor->getExperiment();

		if ($this->currentUser and $experiment->canEditSettings($this->currentUser)) {

			$experiment->moveToPreviousStage();
			$this->repository->save($experiment);

		} else {

			throw new \Karolina\Exception("Current user is not authorized to change the stage", "access_denied", 401);
		}

	}

	public function addTag ($tagId) {

		$experiment = $this->interactor->getExperiment();

		if ($this->currentUser and $experiment->canEditContent($this->currentUser)) {

			$tagRepository = new \Karolina\Tag\TagRepository();
			$tag = $tagRepository->getById($tagId);
			if (! $tag) {
				throw new \Karolina\Exception("Tag not found", "not_found", 404);
			}

			$experiment->addTag($tag);
			$this->repository->save($experiment);

		} else {

			throw new \Karolina\Exception("Current user is not authorized to update tags", "access_denied", 401);
		}

	}

	public function removeTag ($tagId) {

		$experiment = $this->interactor->getExperiment();

		if ($this->currentUser and $experiment->canEditContent($this->currentUser)) {

			$tagRepository = new \Karolina\Tag\TagRepository();
			$tag = $tagRepository->getById($tagId);
			if (! $tag) {
				throw new \Karolina\Exception("Tag not found", "not_found", 404);
			}
			$experiment->removeTag($tagId);
			$this->repository->save($experiment);
		} else {
			throw new \Karolina\Exception("Current user is not authorized to update tags", "access_denied", 401);
		}
	}

	public function replaceTags ($tagIdCollection) {

		$experiment = $this->interactor->getExperiment();

		if ($this->currentUser and $experiment->canEditContent($this->currentUser)) {

			$tagRepository = new \Karolina\Tag\TagRepository();
			$tags = $tagRepository->getTagsFromTagIdCollection($tagIdCollection);
			$experiment->replaceTags($tags);
			$this->repository->save($experiment);

		} else {

			throw new \Karolina\Exception("Current user is not authorized to update tags", "access_denied", 401);
		}

	}

	public function replaceLinks ($links) {

		$experiment = $this->interactor->getExperiment();

		if ($this->currentUser and $experiment->canEditContent($this->currentUser)) {

			$experiment->setLinks($links);
			$this->repository->save($experiment);

		} else {

			throw new \Karolina\Exception("Current user is not authorized to update tags", "access_denied", 401);
		}

	}

	public function updateFunding ($funding) {

		$experiment = $this->interactor->getExperiment();

		if ($experiment->canEditFunding($this->currentUser)) {

			if (isset($funding['goal'])) {

				$experiment->setFundingGoalAmount($funding['goal']);

			}

			if (isset($funding['currency'])) {

				$experiment->setFundingGoalCurrency($funding['currency']);

			}

			if (isset($funding['sources']['state'])) {

				$experiment->setStateFunding($funding['sources']['state']['raised']);
			}

			if (isset($funding['sources']['organizations'])) {

				$experiment->setOrganizationsFunding($funding['sources']['organizations']['raised']);
			}

			if (isset($funding['sources']['crowd']['api'])) {


				if ($funding['sources']['crowd']['api'] === NULL or $funding['sources']['crowd']['api'] == "") {

					$experiment->setCrowdFunding(NULL, NULL);


				} else if (isset($funding['sources']['crowd']['api']) and isset($funding['sources']['crowd']['campaign_id'])) {

					$apiUrl = $funding['sources']['crowd']['api'];
					$campaignId = $funding['sources']['crowd']['campaign_id'];
					$experiment->setCrowdFunding($apiUrl, $campaignId);

				} else {

					throw new \Karolina\Exception('Please set both campaign ID and platform API URL.');

				}

			}

		}

		$this->repository->save($experiment);

	}

	public function updateFundingGoalAmount ($goal) {

		$experiment = $this->interactor->getExperiment();

		if ($experiment->canEditFunding($this->currentUser)) {

			$experiment->setFundingGoalAmount($goal);

		}
		$this->repository->save($experiment);

	}

	public function updateFundingGoalCurrency ($currency) {

		$experiment = $this->interactor->getExperiment();

		if ($experiment->canEditFunding($this->currentUser)) {

			$experiment->setFundingGoalCurrency($currency);

		}
		$this->repository->save($experiment);

	}


	public function updateFundingSources ($fundingSources) {


		$experiment = $this->interactor->getExperiment();

		if ($experiment->canEditFunding($this->currentUser)) {

			if (isset($fundingSources['state'])) {

				$experiment->setStateFunding($fundingSources['state']['raised']);
			}

			if (isset($fundingSources['organizations'])) {

				$experiment->setOrganizationsFunding($fundingSources['organizations']['raised']);
			}

			if (isset($fundingSources['crowd'])) {

				$apiUrl = $fundingSources['crowd']['api'];
				$campaignId = $fundingSources['crowd']['campaign_id'];

				$experiment->setCrowdFunding($apiUrl, $campaignId);

			}


		}

		$this->repository->save($experiment);

	}


	public function updateCustomLanguage ($customLanguageFields, $langCode) {

		$experiment = $this->interactor->getExperiment();

		if ($experiment->canEditContent($this->currentUser)) {

			foreach ($customLanguageFields as $fieldName => $content) {

				$experiment->setCustomLanguage($fieldName, $langCode, $content['value'], $content['format']);
			}

			$this->repository->save($experiment);

		} else {

			throw new \Karolina\Exception("Current user is not authorized to update custom language", "access_denied", 401);
		}

	}


	public function updateDefaultLanguage ($defaultLanguageFields, $langCode) {

		$experiment = $this->interactor->getExperiment();

		if ($experiment->canEditContent($this->currentUser)) {

			if (isset($defaultLanguageFields['title']['value'])) {
				$experiment->setTitle($defaultLanguageFields['title']['value'], $langCode);
			}

			if (isset($defaultLanguageFields['short_description']['value'])) {
				$experiment->setShortDescription($defaultLanguageFields['short_description']['value'], $langCode);
			}

			if (isset($defaultLanguageFields['owner_name']['value'])) {
				$experiment->setOwnerName($defaultLanguageFields['owner_name']['value'], $langCode);
			}


			$this->repository->save($experiment);

		} else {

			throw new \Karolina\Exception("Current user is not authorized to update language", "access_denied", 401);
		}

	}

}
