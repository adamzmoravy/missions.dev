<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;

Class Setting extends Model {

    protected $table = 'kf_settings';
    protected $primaryKey = 'settings_id';
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


}