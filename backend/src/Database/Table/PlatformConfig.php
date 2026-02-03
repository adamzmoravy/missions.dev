<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;

Class PlatformConfig extends Model {

    protected $table = 'kf_platform_config';
    protected $primaryKey = 'id';
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::setEventDispatcher( new \Illuminate\Events\Dispatcher() );
        static::creating(function ($model) {

            $model->platform = getenv('platform_key');
        });

        static::addGlobalScope(new \Karolina\Database\PlatformScope);


    }

    public function getValueArray () {

        return json_decode($this->value, true);

    }

    public function getValueField ($field) {

        $array = $this->getValueArray();

        return $array[$field];
    }

}