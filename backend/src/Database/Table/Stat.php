<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;

Class Stat extends Model {

    protected $table = 'kf_stats';
    protected $primaryKey = 'stat_id';
    protected $guarded = [];
    public $timestamps = false;


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