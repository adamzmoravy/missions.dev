<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;
use Karolina\Language\LanguageFields;
use \Karolina\UUID;

Class Platform extends Model {

    protected $table = 'kf_platforms';
    protected $primaryKey = 'platform_id';
    protected $guarded = [];


    protected static function boot()
    {
        parent::boot();

        static::setEventDispatcher( new \Illuminate\Events\Dispatcher() );
        static::creating(function ($model) {

            $uuid = new UUID();
            $model->uuid = $uuid;

        });

    }

    public function configs()
    {
        return $this->hasMany(PlatformConfig::class, 'platform', 'platform_key')->withoutGlobalScopes();
    }

}