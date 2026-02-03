<?php

namespace Karolina\Accounting;
use \Karolina\Database\Table\Resource as Model;

Class ResourceRepository {


    public function getResourceById ($id) {

        $model = Model::findOrFail($id);

        if ($model) {

            $resource = $this->modelToResource($model);

        }

        return $resource;

    }

    public function getResourcesByType ($type) {
        
        if ($type == 'all') {

            // order by id desc
            $models = Model::orderBy('resource_id', 'desc')->get();

        } else {

            $models = Model::where('type', $type)->get();

        }

        $resources = array();

        foreach ($models as $model) {

            $resources[] = $this->modelToResource($model);

        }
        
        return $resources;

    }


    public function saveResource (Resource $resource) {

        $model = $this->resourceToModel($resource);
        $model->save();

    }


    private function modelToResource ($model) {
            
            $resource = new Resource();
            $resource->__set('id', $model->resource_id);
            $resource->__set('data', $model->data);
            $resource->setName($model->name);
            $resource->setType($model->type);
            $resource->setStatus($model->status);            
            $resource->setPrice((string) $model->price);

            if ($model->language) {
                $resource->setCustomLanguageFieldsFromArray(json_decode($model->language, true));
            }
            if ($model->images) {
                $resource->setImageCollectionFromDocument(json_decode($model->images, true));
            }

            foreach ($model->bookings as $booking) {
    
                $resource->addBooking($booking);
    
            }
    
            return $resource;
    
    }

    private function resourceToModel (Resource $resource) {

        $model = Model::updateOrCreate(
            ['resource_id' => $resource->getId()],
            $resource->getDocument()
        );

        $model->language = json_encode($resource->__get('customLanguageFields')->getDocument());
        $model->images = json_encode($resource->getImageCollectionDocument());
        $model->data = $resource->__get('data');
        
        foreach ($resource->getBookings() as $booking) {

            $model->bookings()->updateOrCreate(
                ['booking_id' => $booking->getId()],
                $booking->getDocument()
            );
        }

        return $model;
    }


}