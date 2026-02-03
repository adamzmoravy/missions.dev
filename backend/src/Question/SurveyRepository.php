<?php

namespace Karolina\Question;

use \Karolina\Database\Table\SurveyResults as Model;



Class SurveyRepository
{

    public function __construct()
    {



    }

    public function submitResults ($answers, $questions, $surveyName) {

        $model = new Model;

        $model->answers = json_encode($answers);
        $model->questions = json_encode($questions);
        $model->survery_name = (string) $surveyName;

        $model->save();
    
    }

    public function getResults ($surveyName) {

        $model = new Model;

        $results = $model->where('survery_name', '=', $surveyName)->get();

        return $results;

    }
}