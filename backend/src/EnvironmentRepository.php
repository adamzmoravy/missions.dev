<?php
namespace Karolina;

use Karolina\Database\Table\EnvironmentVariables;
use Karolina\Database\Table\Hosts;
use Respect\Validation\Validator as v;

class EnvironmentRepository
{

    public function newEnvironmentVariable ($collection, $name, $value) {

        $collection = (string) new \Karolina\Keyname($collection);
        $name = (string) new \Karolina\Keyname($name);

        $model = new EnvironmentVariables();

        $model->name = $name;
        $model->value = $value;
        $model->collection = $collection;

        $model->save();


    }

    public function newHost ($hostname, $collection) {

        $model = new Hosts();

        if (v::domain()->validate($hostname)) {
            $model->hostname = $hostname;

        } else {
            throw new \Karolina\Exception('Hostname invalid. '.$hostname, 'invalid_hostname', 500);
        }

        $collection = (string) new \Karolina\Keyname($collection);

        $model->collection = $collection;

        $model->save();


    }

}