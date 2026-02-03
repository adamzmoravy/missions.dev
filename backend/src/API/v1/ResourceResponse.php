<?php

namespace Karolina\API\v1;

Class ResourceResponse extends Response {

    private $event;


    public function __construct ($resource = null) {

        $this->resource = $resource;

    }

    public function get () {

        $doc = $this->resource->getDocument();
        $r['resource_id'] = (string) $this->resource->getId();

        $r['name'] = (string) $doc['name'];
        $r['status'] = (string) $doc['status'];
        $r['type'] = (string) $this->resource->getType();
        $r['price'] = (string) $doc['price'];
        $r['currency'] = (string) $doc['currency'];
        $r['description'] = (string) $this->resource->getCustomLanguage('description');
        $r['image'] = (string) $this->resource->getMainImage();
        $r['bookable_by'] = $this->resource->getBookableBy();
        $r['bookings'] = array();

        foreach ($this->resource->getBookings() as $booking) {

            $r['bookings'][] = (new BookingResponse($booking))->get();

        }

        return $r;

    }

    public function getFieldsMeta () {

        $fields['resource_id'] = array(
            'type' => 'string',
            'label' => 'ID',
            'description' => 'The resource id',
            'editable' => false
        );

        $fields['name'] = array(
            'type' => 'string',
            'label' => 'Name',
            'description' => 'The name of the resource',
            'editable' => true
        );


        $fields['description'] = array(
            'type' => 'string',
            'label' => 'Description',
            'description' => 'Description of the resource',
            'editable' => true
        );


        $fields['image'] = array(
            'type' => 'string',
            'label' => 'Main image',
            'description' => 'An image of the resource',
            'editable' => true
        );

        $fields['price'] = array(
            'type' => 'string',
            'description' => 'Price per hour',
            'label' => 'Price / hr',
            'editable' => true
        );


        $fields['type'] = array(
            'type' => 'string',
            'description' => 'The type of the resource',
            'label' => 'Type',
            'editable' => true
        );

        return $fields;

    }

}

Class BookingResponse {

    private $booking;

    public function __construct ($booking) {

        $this->booking = $booking;

    }

    public function get () {

        $r['booking_id'] = (string) $this->booking->getId();
        $r['start'] = (string) $this->booking->getStart();
        $r['end'] = (string) $this->booking->getEnd();
        $r['user_id'] = (string) $this->booking->getUserId();
        $r['description'] = (string) $this->booking->getDescription();

        return $r;

    }

    public function getFieldsMeta () {

        $fields['booking_id'] = array(
            'type' => 'number',
            'label' => 'ID',
            'description' => 'The booking id',
            'editable' => false
        );

        $fields['start'] = array(
            'type' => 'string',
            'label' => 'Start',
            'description' => 'The start of the booking',
            'editable' => true
        );


        $fields['description'] = array(
            'type' => 'string',
            'label' => 'Reason',
            'description' => 'Description of the booking',
            'editable' => true
        );


        $fields['end'] = array(
            'type' => 'string',
            'label' => 'End',
            'description' => 'The end of the booking',
            'editable' => true
        );

        $fields['user_id'] = array(
            'type' => 'string',
            'label' => 'User ID',
            'description' => 'The user id of the user who booked the resource',
            'editable' => true
        );

        return $fields;

    }

}