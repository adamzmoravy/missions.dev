<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;

Class Hosts extends Model {

    protected $table = 'hosts';
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