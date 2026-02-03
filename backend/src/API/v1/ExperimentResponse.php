<?php

namespace Karolina\API\v1;

use Karolina\Platform;

Class ExperimentResponse extends Response {

	private $document;
	private $platform;
	private $langCode;

	public function __construct ($document, $langCode, Platform $platform) {

		$this->document = $document;
		$this->platform = $platform;
		$this->langCode = $langCode;

	}

	public function getPreview ($showTeamEmails = false) {

		$doc = $this->document;
		$imgStorageUrl = $this->platform->getImgStorageUrl();

		$response['stage'] = (int) $doc['stage'];
		$response['uuid'] = $doc['uuid'];

		$response['like_count'] = (int) $doc['like_count'];

		if (isset($doc['comment_count'])) {
            $response['comment_count'] = (int) $doc['comment_count'];
        } else {
            $response['comment_count'] = null;
        }

		if (isset($doc['relevance'])) {
            $response['relevance'] = (int) $doc['relevance'];
        } else {
            $response['relevance'] = null;
        }

		if (isset($doc['last_major_update'])) {
            $response['last_major_update'] = (int) $doc['last_major_update'];
        } else {
            $response['last_major_update'] = 0;
        }

		$response['created_at'] = $doc['created_at'];


		$response['experiment_id'] = (string) $doc['experiment_id'];


		if (isset($doc['status'])) {
			$response['status'] = (string) $doc['status'];
		} else {
			$response['status'] = "published";
		}

		$language = (new LanguageResponse($doc['language']))->get($this->langCode);

		if (isset($language['title']['value'])) {
			$response['title'] = $language['title']['value'];
		} else {
			$response['title'] = "Untitled";
		}

		if (isset($language['short_description']['value'])) {
			$response['short_description'] = $language['short_description']['value'];
		} else {
			$response['short_description'] = "";
		}

		if (isset($language['owner_name']['value'])) {
			$response['owner_name'] = $language['owner_name']['value'];
		} else {
			$response['owner_name'] = "";
		}


		$images = (new ImageCollectionResponse($doc['image_collection'], $imgStorageUrl))->get();

		if (isset($images['top_image']['url'])) {
			$response['image'] = $images['top_image']['url'];
		} else {
			$response['image'] = NULL;
		}

		if ((isset($doc['geographic_location']) and $doc['geographic_location'] != NULL)) {
            $response['geographic_location'] = $doc['geographic_location'];
        } else {
            $response['geographic_location'] = NULL;
        }

		$response['show_in'] = $doc['show_in'];
		$response['show_in_placement'] = $doc['show_in_placement'];

		if (isset($doc['team_emails']) && $showTeamEmails){
			$response['team_emails'] = $doc['team_emails'];
		}else{
			$response['team_emails'] = NULL;
		}
		
        $response['team'] = (new TeamResponse($doc['team'], $imgStorageUrl))->get();
		$response['team_ids'] = (new TeamResponse($doc['team'], $imgStorageUrl))->getIds();

		$response['tags'] = (new TagsResponse($doc['tags'], $this->langCode))->get();

		$participants = $this->getParticipants();
		$response['participants']['like_count'] = count($participants['like']['array']);
		$response['participants']['updates_count'] = count($participants['updates']['array']);
		$response['participants']['help_count'] = count($participants['help']['array']);

		if (isset($doc['visibility'])) {
			$response['visibility'] = (string) $doc['visibility'];
		} else {
			$response['visibility'] = "public";
		}

        return $response;


	}

	public function getAdmin () {


        $response = $this->getPreview(true);
        $participants = $this->getParticipants();

        $response['like_count'] = count($participants['like']['array']);
        $response['updates_count'] = count($participants['updates']['array']);
        $response['help_count'] = count($participants['help']['array']);
        $response['interactions_total'] = $response['like_count'] + $response['updates_count'] + $response['help_count'] + $response['comment_count'];

        return $response;


	}

	public function get () {

		$doc = $this->document;
		$imgStorageUrl = $this->platform->getImgStorageUrl();

        $response['uuid'] = (string) $doc['uuid'];

		$response['stage'] = (int) $doc['stage'];
		$response['created_at'] = $doc['created_at'];
		$response['updated_at'] = $doc['updated_at'];
		$response['stage'] = (int) $doc['stage'];
		$response['geographic_location'] = $doc['geographic_location'];

		if (isset($doc['status'])) {
			$response['status'] = (string) $doc['status'];
		} else {
			$response['status'] = "published";
		}

		$response['like_count'] = (int) $doc['like_count'];

		if (isset($doc['visibility'])) {
			$response['visibility'] = (string) $doc['visibility'];
		} else {
			$response['visibility'] = "public";
		}

		$response['experiment_id'] = (string) $doc['experiment_id'];
		$response['custom_language'] = (new LanguageResponse($doc['custom_language']))->get($this->langCode);
		$response['language'] = (new LanguageResponse($doc['language']))->get($this->langCode);

		$response['image_collection'] = (new ImageCollectionResponse($doc['image_collection'], $imgStorageUrl))->get();
		$response['team'] = (new TeamResponse($doc['team'], $imgStorageUrl))->get();
		$response['team_ids'] = (new TeamResponse($doc['team'], $imgStorageUrl))->getIds();

		$response['translations'] = $doc['language'];

        $response['related_experiments'] = [];
        foreach ($doc['relationships']['related_experiments'] as $id => $type) {
            $response['related_experiments'][] = (string) $id;
        }

		$response['disabled'] = (bool) $doc['disabled'];

		if (count($doc['show_in']) == 0) {

			$response['show_in'] = NULL;

		} else {

			$response['show_in'] = $doc['show_in'];

		}


		$response['funding'] = (new ExperimentFundingResponse($doc['funding']))->get();

		$response['tags'] = (new TagsResponse($doc['tags'], $this->langCode))->get();

		$response['links'] = $doc['links'];


		if (isset($doc['attachments']) && $doc['attachments'] != NULL) {
			$response['attachments'] = $doc['attachments'];
		} else {
			$response['attachments'] = [];
		}


		if (isset($doc['settings']['restrictions'])) {
            $response['restrictions'] = $doc['settings']['restrictions'];
        } else {
            $response['restrictions'] = [];
        }



        $participants = $this->getParticipants();
        $response['participants'] = $this->getParticipants();

//		$response['relationships'] = $doc['relationships'];


		return $response;

	}

	private function getParticipants () {

	    $doc = $this->document;

	    $relationships = ['like', 'updates', 'help'];

	    foreach ($relationships as $type) {
            $participants[$type]['array'] = [];
            $participants[$type]['string'] = "";

            foreach ($doc['relationships'][$type] as $id => $it) {
                $participants[$type]['array'][] = $id;
                $participants[$type]['string'] .= $id.",";
            }

        }


        return $participants;

    }

	public function getMyRelationships ($currentUser) {

		if (isset($this->document['relationships'])) {
			$relationshipsDoc = $this->document['relationships'];

		}
		$relationships = array();

		if ($currentUser) {

			if (isset($relationshipsDoc['like'][$currentUser->getId()])) {

				$relationships[] = "like";

			}

            if (isset($relationshipsDoc['help'][$currentUser->getId()])) {

                $relationships[] = "help";

            }

            if (isset($relationshipsDoc['updates'][$currentUser->getId()])) {

                $relationships[] = "updates";

            }
		}

		return $relationships;

	}
	public function getACL ($currentUser) {

		$doc = $this->document;
		$acl = ['view'];

		if ($currentUser) {
			// If user is in the team or is admin, invite to edit
			if (isset($doc['team'][$currentUser->getId()]) or $currentUser->isAdmin()) {
				$acl[] = 'edit';
			}

			// If user is in the team, add edit
			if ($currentUser->isAdmin()) {
				$acl[] = 'admin';
			}
		}

		return $acl;

	}

}
