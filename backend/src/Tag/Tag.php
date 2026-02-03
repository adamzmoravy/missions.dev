<?php

namespace Karolina\Tag;
use Karolina\Language\LanguageFields;
use Karolina\Keyname;

Class Tag {

	private $id;
	private $langFields;
	private $enabled = true;
	private $type = "experiment";
	private $types = [];


	public function getTypes()
	{

		return $this->types;
	}

	public function setTypes($types)
	{
		$this->types = [];

		if ($types == null) {
			return;
		}

		foreach ($types as $type) {
			$this->addType($type);
		}
	}

	public function addType($type) {

		if (in_array($type, $this->types)) {
			return;
		}
		$this->types[] = (string) new Keyname($type);
	}

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

	public function __construct () {

		$this->id = NULL;
		$this->langFields = new LanguageFields();

	}

	public function setEnabled ($enabled) {

	    $this->enabled = $enabled;
    }

    public function getEnabled () {
	    return $this->enabled;
    }

    public function disable () {
	    $this->enabled = false;
    }

	public function setId ($id) {

		$this->id = $id;

	}

	public function getId () {

		return $this->id;

	}

	public function getLabelDocument () {

		return $this->langFields->getDocument();
	}

	public function getLabelLanguageFields () {

		return $this->langFields->getArray();

	}

	public function setLabelLanguageFields ($langFieldsArray) {

		$this->langFields =  new \Karolina\Language\LanguageFields($langFieldsArray);

	}

	public function setLabel ($value, $langCode = "en") {

		$this->langFields->set('label', $langCode, $value, 'plaintext');

	}

	public function getLabel ($langCode = "en") {

		return $this->langFields->get('label', $langCode, 'Label')->getValue();

	}

}
