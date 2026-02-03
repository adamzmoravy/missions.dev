<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

Class SurveyResults extends Model {

    protected $table = 'kf_survey_results';
    protected $primaryKey = 'survey_results_id';
    protected $guarded = [];


    protected static function boot()
    {
        parent::boot();

        static::setEventDispatcher( new \Illuminate\Events\Dispatcher() );
        static::creating(function ($model) {

            $model->platform = getenv('platform_key');
        });

        static::addGlobalScope(new \Karolina\Database\PlatformScope);


    }


    public function getAnswersAttribute($value)
    {
        return json_decode($value, true);
    }    

    public function getQuestionsAttribute($value)
    {
        return json_decode($value, true);
    }    

}