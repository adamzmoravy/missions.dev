<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


Class Experiment extends Model {

    protected $table = 'kf_experiments';
    protected $primaryKey = 'experiment_id';
    protected $guarded = [];
    protected $attributes = [];

    protected static function boot()
    {
        parent::boot();

        static::setEventDispatcher( new \Illuminate\Events\Dispatcher() );
        static::creating(function ($model) {

            $model->platform = getenv('platform_key');
        });

        static::addGlobalScope(new \Karolina\Database\PlatformScope);

        static::addGlobalScope('disabled', function (Builder $builder) {
            $builder->where('disabled', '=', 0);
        });


    }

}