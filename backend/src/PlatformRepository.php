<?php

namespace Karolina;
use Karolina\Database\Table\PlatformConfig;
use Noodlehaus\Config;
use Respect\Validation\Validator as v;

Class PlatformRepository {

    private $databaseTable;

    public function __construct()
    {
        $this->databaseTable = new PlatformConfig();
    }

    private function platformExists ($key) {

        $model = \Karolina\Database\Table\Platform::where('platform_key', $key)->get();

        if (count($model) > 0) {
            return true;
        } else {
            return false;
        }

    }

    public function newPlatform ($key) {

        if ($this->platformExists($key)) {
            throw new Exception('A platform with this key already exists: '.$key, 'platform_exists', '500');
        }

        $model = new \Karolina\Database\Table\Platform();
        $model->platform_key = $key;
        $model->status = "preparing";
        $model->save();

        return $this->modelToPlatform($model);
    }

    public function getPlatformByKey ($key) {

        $model = \Karolina\Database\Table\Platform::where('platform_key', $key)->firstOrFail();
        return $this->modelToPlatform($model);

    }

    public function getPlatforms () {

        $models = \Karolina\Database\Table\Platform::all();

        $platforms = [];

        foreach ($models as $model) {

            $platforms[] = $this->modelToPlatform($model);
        }
        return $platforms;

    }

    private function modelToPlatform ($model) {

        $platform = new Platform();

        $platform->setKey($model->platform_key);
        foreach ($model->configs as $config) {

            $platform->setDBConf($config->name, json_decode($config->value, true));

        }

        return $platform;
    }

}

