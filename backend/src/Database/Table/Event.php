<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;

Class Event extends Model {

    protected $table = 'kf_events';
    protected $primaryKey = 'event_id';
    protected $guarded = [];
    public $timestamps = true;


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