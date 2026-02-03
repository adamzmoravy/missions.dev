<?php

namespace Karolina\Setting;

Class SettingRepository {

	public function getAll () {

		$settingsGroup = new \Karolina\Setting\SettingsGroup();

    	$models = \Karolina\Database\Table\Setting::all();

    	foreach ($models as $model) {

    		$settingsGroup->set($model->variable, $model->value);

    	}

    	return $settingsGroup;

	}

	public function getAllTranslations () {

		$languages = array();

    	$models = \Karolina\Database\Table\Translation::all();

		foreach ($models as $model) {

			$languages[$model->language][$model->key] = $model->value;

		}

		return $languages;


	}

	public function updateTranslation ($key, $language, $value) {

		$translation = \Karolina\Database\Table\Translation::where('key', $key)->where('language', $language)->first();

		if (!$translation) {

			$translation = new \Karolina\Database\Table\Translation();
			$translation->key = $key;
			$translation->language = $language;

		}

		$translation->value = strip_tags($value, '<strong><p><a><h2><h3><h4>');

		if ($value == "") {

			$translation->delete();

		} else {

			$translation->save();
			
		}


	}

}