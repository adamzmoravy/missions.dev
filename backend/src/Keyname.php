<?php

namespace Karolina;
use Respect\Validation\Validator as v;

Class Keyname {

	private $name;

	public function __construct ($name, $type = 'unknown') {

        $this->name = $this->validate($name);

	}

	public function __toString() {
		return (string) $this->name;
	}

	
	static function validateConfigKey ($name) {

		if (v::alnum('_.-')->noWhitespace()->validate($name)) {

			return $name;

		} else {

			throw new Exception('Key name for config must only contain alphanum, dash and underscores. Not allowed: '.htmlentities($name), 'invalid_arguments');
		}
	}

    static function validate ($name, $type = 'unknown') {

        if (v::alnum('_.-')->noWhitespace()->validate($name)) {

           return $name;

        } else {

            throw new Exception('Key name for '.$type.' must only contain alphanum, underscores, dots or dashes. Not allowed: '.htmlentities($name), 'invalid_arguments');
        }
    }
	public function get () {
		return (string) $this->name;
	}

}