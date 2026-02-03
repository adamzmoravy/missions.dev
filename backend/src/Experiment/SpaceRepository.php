<?php

namespace Karolina\Experiment;

use Karolina\Database\Table\Space as Table;
use Karolina\Experiment\Space;
use Karolina\Language\LanguageFields;

class SpaceRepository
{


    public function removeSpace ($spaceId)
    {
        $model = Table::findOrFail($spaceId);
        $model->delete();
    }

    public function createSpace (String $title) {

        $space = new Space();
        $space->setCustomLanguage('title', 'en', $title);
        $model = $this->spaceToModel($space);
        $model->save();

    }

    public function getSpaceById ($id) {

        $model = Table::findOrFail($id);
        return $this->modelToSpace($model);
    }

    public function getSpaces () {

        $models = Table::orderBy('space_id', 'DESC')->get();

        $spaces = [];

        foreach ($models as $model) {

            $spaces[] = $this->modelToSpace($model);

        }

        return $spaces;

    }

    private function spaceToModel (\Karolina\Experiment\Space $space) {

        $model = new Table;
        $model->custom_language_fields = $space->__get('customLanguageFields');

        return $model;
    }

    private function modelToSpace ($model) {

        $space = new Space();
        $space->setCustomLanguageFieldsFromString($model->custom_language_fields);
        $space->__set('id', $model->space_id);
        $space->__set('uuid', $model->uuid);
        $space->__set('key', $model->key);
        return $space;


    }
}