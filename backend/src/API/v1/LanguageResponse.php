<?php

namespace Karolina\API\v1;

Class LanguageResponse extends Response {

	private $document;
		
	public function __construct ($document) {

		$this->document = $document;

	}
 
 	public function get ($langCode) {

 		$doc = $this->document;
 		$langCode = strtoupper($langCode);

 		$response = array();

 		// Return the language for that languge code
		if (isset($doc[$langCode])) {

			$response = $doc[$langCode];

		} else { // If it doesn't exist, just return any language that exists

			$response = current($doc);

		}

		return $response;

 	}

	public function getAllLanguagesResponse () {

		$doc = $this->document;
		$response = array();

		foreach ($doc as $langCode => $languageKeys) {

			foreach ($languageKeys as $key => $values) {
				
				$response[$key][$langCode] = $values;
			}

		}


		return $response;


	}

 }
