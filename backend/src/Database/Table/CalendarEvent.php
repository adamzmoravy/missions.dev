<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

Class CalendarEvent extends Model {

    protected $table = 'kf_calendar_events';
    protected $primaryKey = 'calendar_event_id';
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

    public function setDataAttribute($value)
    {
        $this->attributes['data'] = json_encode($value);
    }    

    public function setCalendarsAttribute($value)
    {
        $this->attributes['calendars'] = json_encode($value);
    }    

    public function getDataAttribute($value)
    {
        return json_decode($value, true);
    }    

    public function getCalendarsAttribute($value)
    {
        return json_decode($value, true);
    }    


}