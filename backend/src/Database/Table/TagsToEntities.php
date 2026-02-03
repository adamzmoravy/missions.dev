<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

Class TagsToEntities extends Model {

    protected $table = 'kf_tags_to_entities';
    protected $primaryKey = 'tags_to_entities_id';
    protected $guarded = [];
    protected $attributes = [];

    public function tag()
    {
        return $this->hasOne('Karolina\Database\Table\Tag', 'tag_id', 'tag_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::setEventDispatcher( new \Illuminate\Events\Dispatcher() );
        static::creating(function ($model) {

            $model->platform = getenv('platform_key');
        });

        static::addGlobalScope(new \Karolina\Database\PlatformScope);

        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('active', '=', 1);
        });

    }




}