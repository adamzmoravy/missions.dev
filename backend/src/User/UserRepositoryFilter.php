<?php


namespace Karolina\User;
use Illuminate\Database\Eloquent\Builder;


class UserRepositoryFilter {

	private $models;

	public function __construct ($models) {

		$this->models = $models;
		$this->models = $this->models->where('active', 1);

	}

	public function fromArguments ($args) {

		if (isset($args['profile_image_has'])) {
			$this->models = $this->models->where('kf_profiles.images', '!=', null);
		}

		if (isset($args['tag'])) {
			$this->byTag($args['tag']);
		}

		if (isset($args['ids'])) {
			$this->byIds($args['ids']);
		}


		if (isset($args['limit'])) {
			$this->models = $this->models->limit((int)$args['limit']);
		}

		if (isset($args['offset'])) {
			$this->models = $this->models->offset((int)$args['offset']);
		}

		if (isset($args['unsubscribed_to_newsletter'])) {
			if ($args['unsubscribed_to_newsletter'] == false) {
				$this->models = $this->models->whereHas('user',  function (Builder $query) {
					$query->whereNull('settings->unsubscribed_to_newsletter');
				});
			}
			if ($args['unsubscribed_to_newsletter'] == true) {
				$this->models = $this->models->whereHas('user',  function (Builder $query) {
					$query->whereNotNull('settings->unsubscribed_to_newsletter');
				});
			}

		}


	}

	public function byIds ($ids) {

		$idArray = explode(',', $ids);
		$this->models = $this->models->whereIn('id', $idArray);

	}

	public function byTag ($tag) {

		$this->models = $this->models->where('json_contains(tags, "['.$tag.']")');

	}

	public function getModels () {

		return $this->models;
	}

}