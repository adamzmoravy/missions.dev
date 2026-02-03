<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;
use Karolina\Language\LanguageFields;
use \Karolina\UUID;
use Illuminate\Database\Eloquent\SoftDeletes;

Class Space extends Model {

    protected $table = 'kf_spaces';
    protected $primaryKey = 'space_id';
    protected $guarded = [];

    use SoftDeletes;


    /*
    protected $casts = [


        'custom_language_fields' => 'array',
    ];

    public function getCustomLanguageFieldsAttribute () {

        return new LanguageFields(json_decode($this->attributes['custom_language_fields'], true));

    }

    public function setCustomLanguageFieldsAttribute($lang)
    {
        $this->attributes['custom_language_fields'] = json_encode(['what', 'yes']);
    }

*/


    protected static function boot()
    {
        parent::boot();

        static::setEventDispatcher( new \Illuminate\Events\Dispatcher() );
        static::creating(function ($model) {

            $uuid = new UUID();
            $model->uuid = $uuid;
            $model->key = $uuid;
            $model->platform = getenv('platform_key');

        });

        static::addGlobalScope(new \Karolina\Database\PlatformScope);


    }

}