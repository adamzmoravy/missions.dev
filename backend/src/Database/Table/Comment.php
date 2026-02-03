<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

Class Comment extends Model {

    protected $table = 'kf_comments';
    protected $primaryKey = 'id';
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
            $builder->where('active', '=', 1);
        });
    }

    public function setValueJsonAttribute($value)
    {
        $this->attributes['value_json'] = json_encode($value);
    }    

    public function getValueJsonAttribute($value)
    {
        return json_decode($value, true);
    }    

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id', 'comment_id');
    }


}