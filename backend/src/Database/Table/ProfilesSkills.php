<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;

Class ProfilesSkills extends Model {

    protected $table = 'kf_profiles_skills';
    protected $primaryKey = 'profile_skill_id';
    protected $guarded = [];
    public $timestamps = false;


    public function skill () {
    
        return $this->hasOne('Karolina\Database\Table\Skill', 'skill_id', 'skill_id');
        
    }

    protected static function boot()
    {
        parent::boot();

        static::setEventDispatcher( new \Illuminate\Events\Dispatcher() );
        static::creating(function ($model) {

            $model->platform = getenv('platform_key');
        });

//        static::addGlobalScope(new \Karolina\Database\PlatformScope);


    }


}