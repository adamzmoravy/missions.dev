<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;

Class Translation extends Model {

    protected $table = 'kf_translations';
    protected $primaryKey = 'translation_id';
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