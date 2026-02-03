<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

Class Account extends Model {

    protected $table = 'kf_accounts';
    protected $primaryKey = 'account_id';
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

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'account_id', 'account_id');
    }

    public function setDataAttribute($value)
    {
        $this->attributes['data'] = json_encode($value);
    }
    
    public function getDataAttribute($value)
    {
        return json_decode($value, true);
    }    
    

}