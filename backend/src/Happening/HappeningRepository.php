<?php

namespace Karolina\Happening;

use \Karolina\Database\Table\Happening as Model;



Class HappeningRepository
{
    private $languageFieldsRepository;

    private $happeningStore;

    public function __construct()
    {
        $this->languageFieldsRepository = new \Karolina\Language\LanguageFieldsRepository();

    }

    public function createHappening (Happening $happening) {

        $model = new Model;

        $model->name = $happening->getName();
        $model->settings = json_encode($happening->getAllSettings());
        $model->users_registered = json_encode($happening->getRegisteredUsers());
        $model->applications = json_encode($happening->getApplications());
        $model->uuid = $happening->getUUID();
        $model->platform = getenv('platform_key');

        $model->save();

        \Karolina\Event\Publisher::instance()->publish(new HappeningUpdatedEvent($happening));

        return (string) $model->happening_id;


    }

    public function save (Happening $happening) {

        $model = Model::findOrFail($happening->getId());

        $model->name = $happening->getName();
        $model->settings = json_encode($happening->getAllSettings());
        $model->users_registered = json_encode($happening->getRegisteredUsers());
        $model->applications = json_encode($happening->getApplications());
        $model->schedule = json_encode($happening->getSchedule());
        $model->uuid = $happening->getUUID();
        $model->active = $happening->getActive();


        // Save custom language fields
        $this->languageFieldsRepository->saveLanguageFields(
            $happening->getCustomLanguageFields());


        $model->save();

        \Karolina\Event\Publisher::instance()->publish(new HappeningUpdatedEvent($happening));

        return true;


    }

    public function getHappeningsByRegistrantId ($id) {


                global $timings;
                $timings->start('happeningRepository::getHappeningsByRegistrantId');

                $timings->start('happeningRepository::getHappeningsByRegistrantId::users_registered');

        $models = Model::whereJsonLength('users_registered->'.$id, '>', '0')->get();

                $timings->end('happeningRepository::getHappeningsByRegistrantId::users_registered');

                $timings->start('happeningRepository::getHappeningsByRegistrantId::foreach::modelToHappening');

        $happenings = [];
        foreach ($models as $model) {
            $happenings[$model->happening_id] = $this->modelToHappening($model);
        }

                $timings->end('happeningRepository::getHappeningsByRegistrantId::foreach::modelToHappening');

                $timings->end('happeningRepository::getHappeningsByRegistrantId');
        return $happenings;

    }

    public function getAllHappenings () {

        $models = Model::where('active', true)->orderBy('happening_id', 'DESC')->get();

        $happenings = [];
        foreach ($models as $model) {
            $happenings[$model->happening_id] = $this->modelToHappening($model);
        }

        return $happenings;

    }

    private function modelToHappening ($model) {

                global $timings;
                $timings->start('happeningRepository::modelToHappening');
        $happening = new Happening();

        $happening->setId($model->happening_id);
        $happening->setName($model->name);
        $happening->setUsersRegistered(json_decode($model->users_registered, true));
        $happening->setApplications(json_decode($model->applications, true));
        $happening->setSettingsGroup($this->getSettingsGroup($model->settings));
        $happening->setUUID($model->uuid);
        $happening->setSchedule(json_decode($model->schedule, true));
        $happening->setActive($model->active);

                $timings->start('happeningRepository::modelToHappening::getCustomLanguageFields');
                // Retrieve and set custom language fields
        $customLanguageFields =
            $this->languageFieldsRepository->getLanguageFields ('happening', $model->happening_id, 'custom');
        $happening->setCustomLanguageFields($customLanguageFields);

                $timings->end('happeningRepository::modelToHappening::getCustomLanguageFields');

                $timings->end('happeningRepository::modelToHappening');
        return $happening;
    }

    public function getById ($id) {

        $model = Model::findOrFail($id);
        return $this->modelToHappening($model);

    }


    private function getSettingsGroup ($fetchedSettings) {

        $settings = json_decode($fetchedSettings, true);

        $settingsGroup = new \Karolina\Setting\SettingsGroup();

        if ($settings) {
            foreach ($settings as $variableName => $value) {
                $settingsGroup->set($variableName, $value);
            }

        }

        return $settingsGroup;

    }

}