<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;

Class Profile extends Model {

    protected $table = 'kf_profiles';
    protected $primaryKey = 'id';
    public $timestamps = false;


    public function user()
    {
        return $this->hasOne('Karolina\Database\Table\User', 'id', 'user_id');
    }

    public function tagsWithType()
    {
        return $this->hasMany('Karolina\Database\Table\TagsToEntities', 'entity_id', 'id')
                    ->where('entity_type', 'profile');
    }



    protected static function boot()
    {
        parent::boot();

        static::setEventDispatcher( new \Illuminate\Events\Dispatcher() );


        static::creating(function ($model) {
            if (!isset($model->platform)) {
                $model->platform = getenv('platform_key');
            }
        });

        static::addGlobalScope(new \Karolina\Database\PlatformScope);


    }


}