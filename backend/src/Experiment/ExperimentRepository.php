<?php

namespace Karolina\Experiment;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Capsule\Manager as DB;
use Karolina\Keyname;

Class ExperimentRepository {

	private $languageFieldsRepository;

	public function __construct ($ci = false) {

		$this->languageFieldsRepository = new \Karolina\Language\LanguageFieldsRepository();
		$this->questionRepository = new \Karolina\Question\QuestionRepository();
		$this->userRepository = new \Karolina\User\UserRepository($ci);
		$this->teamRepository = new \Karolina\Team\TeamRepository($this->userRepository);
		$this->discussionRepository = new \Karolina\Discussion\DiscussionRepository();

	}



	public function getStats ($scope = 'local') {

	    if ($scope == 'global') {
            $experimentsModel = new \Karolina\Database\Table\Experiment();
            $experimentsModel = $experimentsModel->withoutGlobalScope(\Karolina\Database\PlatformScope::class);

        } else {
            $experimentsModel = new \Karolina\Database\Table\Experiment();
        }


        $stages = $experimentsModel->groupBy('stage')->select('stage', DB::raw('count(*) as total'))->get();


        $stats['stage-0'] = "0";
        $stats['stage-1'] = "0";
        $stats['stage-2'] = "0";
        $stats['stage-3'] = "0";
        $stats['stage-4'] = "0";
        $stats['stage-5'] = "0";
        $stats['stage-6'] = "0";
        $stats['stage-7'] = "0";

		$total = 0;
        foreach ($stages as $stage) {

            $stats['stage-'.$stage->stage] = $stage->total;
			$total = $total + $stage->total;
        }

		$response['total'] = $total;
		$response['stages'] = $stats;

        return $response;

    }

	public function setMeta ($id, $key, $value) {

		$model = new \Karolina\Database\Table\Experiment();

		if (is_array($id)) {
			$model = $model->whereIn('id', $id);
		} else {
			$model = $model->where('id', $id);
		}

		$key = (string) new Keyname($key);
		
		$model->update(['meta->'.$key => $value]);

		return true;

	}

	private function modelToExperiment ($model) {

		$experiment = new \Karolina\Experiment\Experiment();

		$experiment->setStage($model->stage);
		$experiment->setCreatedAt($model->created_at);
		$experiment->setUpdatedAt($model->updated_at);
		$experiment->setStage($model->stage);
		$experiment->setSettingsGroup($this->getSettingsGroup($model->settings));
		$experiment->setId($model->experiment_id);
		$experiment->setUUID($model->uuid);

		$experiment->__set('attachments', json_decode($model->attachments, true));


		if ($model->disabled) {
			$experiment->disable();
		} else {
			$experiment->enable();
		}

		// Places to show
		if ($model->show_in) {
			$placesToShowData = json_decode($model->show_in, true);

			foreach ($placesToShowData as $place => $set) {
				$experiment->setPlaceToShow($place, $set);
			}

		}

        // Places to show placement
        if ($model->show_in_placement) {
            $placesToShowPlacementData = json_decode($model->show_in_placement, true);

            foreach ($placesToShowPlacementData as $place => $placement) {
                $experiment->setPlaceToShowPlacement($place, $placement);
            }
        }

		// Retrieve and set team
		$team = $this->teamRepository->getTeam ('experiment', $model->experiment_id);
		$experiment->setTeam($team);

		// Retrieve and set custom language fields
		$customLanguageFields =
		$this->languageFieldsRepository->getLanguageFields ('experiment', $model->experiment_id, 'custom');
		$experiment->setCustomLanguageFields($customLanguageFields);

		// Retrieve and set default language fields
		$defaultLanguageFields =
		$this->languageFieldsRepository->getLanguageFields ('experiment', $model->experiment_id, 'default');
		$experiment->setLanguageFields($defaultLanguageFields);

		// Retrieve and set questions
		$questions = $this->questionRepository
		->getQuestions ('experiment', $model->experiment_id);
		foreach ($questions as $question) {
			$experiment->addQuestion($question);
		}

		// Retrieve and set image collection
		if ($model->images) {
			$imagesData = json_decode($model->images, true);
			$experiment->setImageCollectionFromDocument($imagesData);
		}

		// Retrieve and set funding
		if ($model->funding) {
			$fundingSources = json_decode($model->funding, true);
			$experiment->setFundingFromDocument($fundingSources);
		}

		// Set links
		if ($model->links) {
			$experiment->setLinks(json_decode($model->links, true));
		}

		// Retrieve and set tags
		if ($model->tags) {
			$tagRepository = new \Karolina\Tag\TagRepository();
			$tags = $tagRepository->getTagsFromTagIdCollection(json_decode($model->tags, true));
			$experiment->replaceTags($tags);
		}

		$experiment->setNetwork($this->getNetwork($experiment->getId()));

        $experiment->setCommentCount($this->discussionRepository->countComments($model->uuid));


		return $experiment;
	}


    private function getSettingsGroup ($fetchedSettings) {

		$settings = json_decode($fetchedSettings, true);

		$settingsGroup = new \Karolina\Setting\SettingsGroup();

		if ($settings) {
			foreach ($settings as $variableName => $value) {
				$settingsGroup->set($variableName, $value);
			}

		}

		return $settingsGroup;

    }

	private function experimentToModel (Experiment $experiment, $model) {


						global $timings;
						$timings->start('experimentToModel');

		$model->stage = $experiment->getStage();
		if($experiment->getCreatedAt()){
			$model->created_at = $experiment->getCreatedAt();
		}
		if($experiment->getUpdatedAt()){
			$model->updated_at = $experiment->getUpdatedAt();
		}

		if ($experiment->isEnabled()) {
			$model->disabled = 0;
		} else {
			$model->disabled = 1;
		}

		$model->attachments = json_encode($experiment->getAttachments());

		// Places to show
		$model->show_in = json_encode($experiment->getPlacesToShow());
        // Places to show placement
        $model->show_in_placement = json_encode($experiment->getPlacesToShowPlacement());

					$timings->start('experimentToModel::saveTeam');
		// Save team
		$team = $this->teamRepository
		->saveTeam ($experiment->getTeam(), 'experiment', $experiment->getId());
					$timings->end('experimentToModel::saveTeam');


					$timings->start('experimentToModel::saveQuestions');
		// Save questions
		foreach ($experiment->getQuestions() as $question) {
			$this->questionRepository->saveQuestion($question);
		}

					$timings->end('experimentToModel::saveQuestions');

					$timings->start('experimentToModel::saveCustomLanguageFields');
		// Save custom langugae fields
		$this->languageFieldsRepository->saveLanguageFields(
			$experiment->getCustomLanguageFields());

					$timings->end('experimentToModel::saveCustomLanguageFields');

					$timings->start('experimentToModel::saveLanguageFields');

		// Save default language fields
		$this->languageFieldsRepository->saveLanguageFields(
			$experiment->getLanguageFields());

					$timings->end('experimentToModel::saveLanguageFields');
				

		// Save funding sources
		$model->funding = json_encode($experiment->getFundingDocument());

		$model->links = json_encode($experiment->getLinks());

		$settings = json_encode($experiment->getAllSettings());

		if ($settings == "[]") {
			$settings = "{}";
		}
		$model->settings = $settings;
        $model->uuid = $experiment->getUUID();

		// Tags
		$tagIdArray = array();
		foreach ($experiment->getTags() as $tag) {
			$tagIdArray[] = $tag->getId();
		}
		$model->tags = json_encode($tagIdArray);

		$model->images = json_encode($experiment->getImageCollectionDocument());
			
					$timings->start('saveNetwork');

		$this->saveNetwork($experiment->getNetwork());

					$timings->end('saveNetwork');



					$timings->end('experimentToModel');

		return $model;

	}

	private function experimentToJsonDocument (Experiment $experiment) {

		$doc['last_updated'] = time();
		$doc['created_at'] = $experiment->getCreatedAt();
		$doc['updated_at'] = $experiment->getUpdatedAt();
		$doc['experiment_id'] = (string) $experiment->getId();
		$doc['stage'] = (string) $experiment->getStage();
		$doc['custom_language'] = $experiment->getCustomLanguageFields()->getDocument();
		$doc['language'] = $experiment->getLanguageFields()->getDocument();
		$doc['image_collection'] = $experiment->getImageCollectionDocument();
		$doc['team'] = $experiment->getTeamDocument();
		$doc['question_contexts'] = $experiment->getQuestionsDocument();
		$doc['show_in'] = $experiment->getPlacesToShow();
        $doc['show_in_placement'] = $experiment->getPlacesToShowPlacement();
		$doc['disabled'] = (int) !$experiment->isEnabled();
		$doc['tags'] = $experiment->getTagsDocument();
		$doc['funding'] = $experiment->getFundingDocument();
		$doc['like_count'] = $experiment->countLikes();
		$doc['comment_count'] = $experiment->getCommentCount();
		$doc['relationships']['like'] = $experiment->getLikers();
        $doc['relationships']['help'] = $experiment->getHelpers();
        $doc['relationships']['updates'] = $experiment->getUpdatesFollowers();
		$doc['relationships']['related_experiments'] = $experiment->getRelatedExperiments();
		$doc['links'] = $experiment->getLinks();
		$doc['geographic_location'] = $experiment->getGeographicLocation();
		$doc['team_emails'] = $experiment->getTeamEmails();
        $doc['uuid'] = (string) $experiment->getUUID();
        $doc['settings'] = $experiment->getAllSettings();
        $doc['relevance'] = $experiment->getRelevance();
        $doc['last_major_update'] = $experiment->getLastMajorUpdate();
        $doc['attachments'] = $experiment->getAttachments();
		$doc['status'] = $experiment->getStatus();
		$doc['visibility'] = $experiment->getVisibility();

		return json_encode($doc);


	}

	public function getDocument ($id, $langCode = false) {

    	try {

			$model = \Karolina\Database\Table\Experiment::findOrFail($id);

    	} catch (ModelNotFoundException $e) {

    		throw new \Karolina\Exception("Submission not found. It may have been removed or you have the wrong URL.", 'not_found', 404);

		}

		catch (\Exception $e) {

    		throw new \Karolina\Exception("There was a problem getting the submission from the database.", 'error', 500);

		}


		return $this->getDocumentFromModel($model, $langCode);

	}

    public function getDocumentByUUID ($uuid, $langCode = false) {

        try {

            $model = \Karolina\Database\Table\Experiment::where('uuid', $uuid)->firstOrFail();

        } catch (ModelNotFoundException $e) {

            throw new \Karolina\Exception("Submission not found. It may have been removed or you have the wrong URL.", 'not_found', 404);

        }

        catch (\Exception $e) {

            throw new \Karolina\Exception("There was a problem getting the experiment from the database.", 'error', 500);

        }


        return $this->getDocumentFromModel($model, $langCode);

    }

	private function getDocumentFromModel ($model, $langCode = false) {


        $document = json_decode($model->document, true);

        if ($langCode) {

            if (isset($document['question_stages']) and $document['question_stages']) {
                foreach ($document['question_stages'] as $keyC => $context) {

                    foreach ($context as $keyQ => $question) {

                        if (isset($question[$langCode])) {

                            $document['question_stages'][$keyC][$keyQ] = $question[$langCode];

                        } else {
                            $document['question_stages'][$keyC][$keyQ] = current($question);
                        }

                    }

                }
            } else {

                $document['question_stages'] = array ();

            }

        }

        return $document;

    }

    public function update ($id) {

					global $timings;
					$timings->start('update::getById');
	    $experiment = $this->getById($id);
					$timings->end('update::getById');

					$timings->start('update::save');
	    $this->save($experiment);
					$timings->end('update::save');
					
	    return $this;
    }

    public function getById($id) {

		$model = \Karolina\Database\Table\Experiment::findOrFail($id);
		$experiment = $this->modelToExperiment($model);
		return $experiment;

	}

    public function getByUUID($uuid) {

        $model = \Karolina\Database\Table\Experiment::where('uuid', $uuid)->firstOrFail();
        $experiment = $this->modelToExperiment($model);

        return $experiment;
    }

    public function getAll () {

        $models = \Karolina\Database\Table\Experiment::all();

        $experiments = [];
        foreach ($models as $model) {
            $experiments[] = $this->modelToExperiment($model);
        }

        return $experiments;
    }



    public function save (Experiment $experiment, $updateType = 'update', $updateData = []) {


				global $timings;
				$timings->start('save::findOrFail');

		$model = \Karolina\Database\Table\Experiment::findOrFail($experiment->getId());

				$timings->end('save::findOrFail');


				$timings->start('save::experimentToModel');

		$model = $this->experimentToModel($experiment, $model);
		
				$timings->end('save::experimentToModel');


				$timings->start('save::experimentToJsonDocument');

		$model->document = $this->experimentToJsonDocument($experiment);

				$timings->end('save::experimentToJsonDocument');

				$timings->start('save::save');

		$model->save();
		
				$timings->end('save::save');

				$timings->start('save::publisher');
				
        \Karolina\Event\Publisher::instance()->publish(new ExperimentUpdatedEvent($experiment, $updateType, $updateData));

				$timings->end('save::publisher');


	}

        // Here we could store the whole document snapshot in S3

    private function getFilteredModels ($filterArguments) {

		$filter = new ExperimentRepositoryFilter(
			new \Karolina\Database\Table\Experiment()
			);

		$filter->fromArguments($filterArguments);

		return $filter->getModels();

	}

    public function getDocuments ($filterArguments) {

		$models = $this->getFilteredModels($filterArguments)->get();

		$docs = array();
		foreach ($models as $model) {
			if ($model->document) {
				$docs[] = json_decode($model->document, true);
			}
		}

		return $docs;

	}

	public function reWriteAllDocuments () {

		$models = \Karolina\Database\Table\Experiment::get();

		foreach ($models as $model) {

			$experiment = $this->modelToExperiment($model);
			$document = $this->experimentToJsonDocument($experiment);
			$model->document = $document;
			$model->save();
		}

		return count($models);

	}

	public function getExperimentStats ($showIn = false) {

			if ($showIn) {

					$stats['count'] = \Karolina\Database\Table\Experiment::whereRaw('json_contains(show_in, "true", "$.'.$showIn.'")')->count();

			} else {

					$stats['count'] = \Karolina\Database\Table\Experiment::count();

			}

	    return $stats;

    }


	public function create (Experiment $experiment) {

		$model = new \Karolina\Database\Table\Experiment;
		$model->save();

		$experiment->setId($model->experiment_id);

		$model = $this->experimentToModel($experiment, $model);
		$model->document = $this->experimentToJsonDocument($experiment);
		$model->save();

		return (string) $model->experiment_id;

	}

	public function getNetwork ($experimentId) {

		$networkRepository = new \Karolina\Network\NetworkRepository();
		$network = $networkRepository->getNetwork('experiments', $experimentId);
		return $network;

	}

	public function saveNetwork ($network) {

		$networkRepository = new \Karolina\Network\NetworkRepository();
		$networkRepository->saveNetwork($network);


	}

	public function massUpdateSetting ($ids, $setting, $value) {

		$models = new \Karolina\Database\Table\Experiment();

		$models = $models->whereIn('experiment_id', $ids);
		$models->update(['settings->'.$setting => $value]);

	}

}
