<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;

Class RateLimitedEvent extends Model {

    protected $table = 'kf_rate_limited_events';
    protected $primaryKey = 'rate_limited_event_id';
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