<?php 

namespace Karolina\User;
use Karolina\Language\Field;

Class Profile {

	private $shortDescription;
	private $description;
	private $active;
	private $settings;  use \Karolina\Setting\SettingTrait;

	public function __construct () {

		$this->setSettingsGroup(new \Karolina\Setting\SettingsGroup());

	}

    public function disable () {

        $this->active = false;

    }

    public function enable () {

        $this->active = true;

    }

    public function isActive () {

        return $this->active;

    }

	public function setMeta ($key, $value) {

		$this->setSetting($key, $value);

	}

	public function metaIsSet ($key) {

		return $this->settingIsSet($key);

	}

	public function getMeta ($key) {

		return $this->settings->get($key);

 	}

	public function getAllMeta () {

		return $this->settings->getAll();

	}

	function setShortDescription ($content) {

		if (is_a($content, 'Karolina\Language\Field')) {

			$this->shortDescription = $content;

		} else {

			$this->shortDescription = new Field($content);

		}


	}


	function setDescription ($content) {

		if (is_a($content, 'Karolina\Language\Field')) {
			
			$this->description = $content;

		} else {

			$this->description = new Field($content);

		}

	}


	function getDescription () {

		return $this->description;

	}

	function getShortDescription () {

			return $this->shortDescription;
		
	}


}