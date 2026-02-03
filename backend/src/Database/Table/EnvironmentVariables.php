<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;

Class EnvironmentVariables extends Model {

    protected $table = 'variables';
    protected $primaryKey = 'id';
    protected $guarded = [];
    protected $connection = 'environments';

    protected static function boot()
    {
        parent::boot();

        static::setEventDispatcher( new \Illuminate\Events\Dispatcher() );
        static::creating(function ($model) {

        });



    }


}