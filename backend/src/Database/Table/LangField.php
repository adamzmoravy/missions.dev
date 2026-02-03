<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;

Class LangField extends Model {

    protected $table = 'kf_lang_fields';
    protected $primaryKey = 'lang_field_id';
    protected $fillable = ['content', 'format', 'content_key', 'lang_code', 'object_id', 'object_type', 'type', 'platform'];


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