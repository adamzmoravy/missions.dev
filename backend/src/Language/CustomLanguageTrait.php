<?php

namespace Karolina\Language;

trait CustomLanguageTrait {
	

    public function getAllCustomLanguage ($langCode = 'EN') {

        return $this->customLanguageFields->getAll($langCode);
    }

    public function getCustomLanguage ($fieldName, $langCode = 'EN') {

        return $this->customLanguageFields->get($fieldName, $langCode);
    }

    public function setCustomLanguage ($fieldName, $langCode, $value, $format = "plaintext") {

        if (!$this->customLanguageFields) {
            $this->customLanguageFields = new LanguageFields();
        }

        $this->customLanguageFields->set($fieldName, $langCode, $value, $format);

    }

    public function setCustomLanguageFields (\Karolina\Language\LanguageFields $fields) {

        $this->customLanguageFields = $fields;

    }

    public function getCustomLanguageFields () {
        return $this->customLanguageFields;
    }


    public function setCustomLanguageFieldsFromString ($string) {

        $this->customLanguageFields = new LanguageFields(json_decode($string, true));

    }

    public function setCustomLanguageFieldsFromArray (array $language) {

        $this->customLanguageFields = new LanguageFields($language);
    }
    
}
