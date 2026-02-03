<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

Class ResourcesBooking extends Model {

    protected $table = 'kf_resources_bookings';
    protected $primaryKey = 'booking_id';
    protected $guarded = [];


    protected static function boot()
    {
        parent::boot();

        static::setEventDispatcher( new \Illuminate\Events\Dispatcher() );
        static::creating(function ($model) {

            $model->platform = getenv('platform_key');
        });

        static::addGlobalScope(new \Karolina\Database\PlatformScope);

        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('status', '!=', 'disabled');
        });
    }


}