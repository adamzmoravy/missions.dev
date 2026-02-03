<?php

namespace Karolina\Image;

use \Karolina\Database\Table\Image as Model;


Class ImageRecordRepository {


	public function saveImageRecord (Image $image, $userId) { 

        $model = new Model();
        $model->filename = $image->getFileName();
        $model->user_id = $userId;
        $model->context = $image->getContentKey();
        $model->save();

    }	

    public function getImageRecordsByContext ($context, $limit = 10) {

        $models = Model::where('context', $context)
        ->orderBy('created_at', 'desc')
        ->take($limit)
        ->get();

        $images = array();

        foreach ($models as $model) {

            $images[] = $this->modelToImage($model);

        }

        return $images;

    }

    private function modelToImage ($model) {

    $image = new Image($model->filename, $model->context);
    $image->__set('id', $model->image_id);
    $image->__set('filename', $model->filename);
    $image->__set('contentKey', $model->context);

    return $image;

    }

}
