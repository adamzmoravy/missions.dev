<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

Class Relationship extends Model {

    protected $table = 'kf_relationships';
    protected $primaryKey = 'relationship_id';
    protected $guarded = [];

//    use SoftDeletes;
//    protected $dates = ['deleted_at'];


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