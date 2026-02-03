<?php 

namespace Karolina\Experiment;

use Carbon\Carbon;

Class ExperimentRepositoryFilter {

	private $models;

	public function __construct ($models) {

		$this->models = $models; 

	}

	public function fromArguments ($args) {

		if (isset($args['team_member_id'])) {

			$this->byTeamMember($args['team_member_id']);
		}

		if (isset($args['place'])) {
			$this->byPlaceToShowIn($args['place']);
		}

		if (isset($args['stage'])) {
			$this->byStage($args['stage']);
		}

		if (isset($args['tag'])) {
			$this->byTag($args['tag']);
		}

		if (isset($args['limit'])) {
			$this->models = $this->models->limit($args['limit' ]);
		}

		if (isset($args['offset'])) {
			$this->models = $this->models->offset((int)$args['offset']);
		}


		if (isset($args['relevance_more_than'])) {

			$this->models = $this->models->where('document->relevance', '>', (int) $args['relevance_more_than']);

		}


		if (isset($args['has_been_sent_in_newsletter'])) {

			if ($args['has_been_sent_in_newsletter'] == false) {

				$this->models = $this->models->whereNull('settings->has_been_sent_in_newsletter');

			} else {

				$this->models = $this->models->whereNotNull('settings->has_been_sent_in_newsletter');

			}

		}

		if (isset($args['newer_than_seconds'])) {
			$current = Carbon::now();
			$past = $current->subSecond((int) $args['newer_than_seconds']);
			$this->models = $this->models->whereDate('created_at', '>=', $past);
		}

		if (isset($args['newer_than_days'])) {
			$current = Carbon::now();
			$past = $current->subDays((int) $args['newer_than_days']);
			$this->models = $this->models->whereDate('created_at', '>=', $past);
		}

		if (isset($args['older_than_days'])) {
			$current = Carbon::now();
			$past = $current->subDays((int) $args['older_than_days']);
			$this->models = $this->models->whereDate('created_at', '<', $past);
		}


		if (isset($args['order_by'])) {

			if ($args['order_by'] == 'most_relevant') {
				$this->models = $this->models->orderByRaw("cast(document->'$.relevance' as UNSIGNED) desc");
			}

			if ($args['order_by'] == 'last_major_update') {
				$this->models = $this->models->orderByRaw("cast(document->'$.last_major_update' as UNSIGNED) desc");
			}

		}

	}

	public function byTeamMember($userId) {

		$this->models = $this->models->where('document->team->'.$userId.'->in_team', true);

	}

	public function byPlaceToShowIn($place) {

		$this->models = $this->models->where('show_in->'.$place	, true);
		
	}

	public function byStage ($stage) {

		$this->models = $this->models->where('stage', "=", (int) $stage);
		
	}

	public function byTag ($tag) {

		$this->models = $this->models->whereRaw('json_contains(tags, "['.$tag.']")');

	}

	public function getModels () {

		return $this->models;
	}

}