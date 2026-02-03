<?php 
namespace Karolina\Accounting;
use Carbon\Carbon;
use Karolina\Keyname;
use Money\Currency;
use Money\Currencies\ISOCurrencies;
use Money\Money;
use Money\Parser\DecimalMoneyParser;
use Karolina\Image\Image;

Class Resource {

    private $bookings = array();
    private $type = 'common';
    private $name = 'Resource';
    private $status = 'active';
    private $price = null;
    private $currency = 'ISK';
    private $id = NULL;
    private $images = array(); use \Karolina\Image\ImageCollectionTrait;
    private $customLanguageFields; use \Karolina\Language\CustomLanguageTrait;
    private $data = array();

    public function __construct () {

    	$this->customLanguageFields = new \Karolina\Language\LanguageFields();


    }

    public function isFreeForAccount ($account_id) {
            
        if (isset($this->data['free_for_accounts'])) {

            if (in_array($account_id, $this->data['free_for_accounts'])) {
                return true;
            } else {
                return false;
            }

        } else {
            return false;

        } 

    }

    public function setBookableBy (array $bookableBy) {

        $this->data['bookable_by'] = $bookableBy;

    }

    public function getBookableBy () {
        
        if (isset($this->data['bookable_by'])) {

            return $this->data['bookable_by'];

        } else {

            return array('users');

        }

    }

    public function getMainImage () {
        if ($this->getImage('main')) {
            return $this->getImage('main')->getFilename();
        }

        return '';
    }

    public function setImage ($filename) {

        $image = new Image($filename, 'main');
        $this->addImage($image);

    }

    public function setDescription ($description) {

        $this->setCustomLanguage('description', 'en', $description, 'html');

    }

    public function setPrice ($price) {

        $currencies = new ISOCurrencies();
        $moneyParser = new DecimalMoneyParser($currencies);
        $this->price = $moneyParser->parse($price, $this->currency);
        
    }

    public function disable () {
            
            $this->status = 'disabled';
    
    }

    public function getName () {

        return $this->name;

    }

    public function setName ($name) {
            
            $this->name = $name;
            return $this;
    }

    public function setType ($type) {
            
            $this->type = $type;
            return $this;
    }

    public function setStatus ($status) {
            
            $this->status = $status;
            return $this;
    }

    public function getBookings () {

        return $this->bookings;

    }

    public function getType () {

        return $this->type;

    }

    public function getId () {

        return $this->id;

    }

    public function removeBooking ($bookingId) {

        foreach ($this->bookings as $key => $booking) {

            if ($booking->getId() == $bookingId) {
                $this->bookings[$key]->disable();
                return true;
            }

        }

        return false;

    }

    public function getBookingById ($bookingId) {

        foreach ($this->bookings as $booking) {

            if ($booking->getId() == $bookingId) {
                return $booking;
            }

        }

        throw new \Karolina\Exception('Booking not found');

    }

    public function addBooking ($booking) {

        $this->bookings[] = new Booking($booking->start, $booking->end, $booking->user_id, $booking->booking_id, $booking->description, $booking->accounting_id);

    }

    public function makeBookingByMinute ($start, $end, $userId, $description, $accountingId) {


        if ($this->isBooked($start, $end)) {
            throw new \Exception("Resource is already booked");
        }

        $booking = new Booking(
            $start,
            $end,
            $userId,
            null,
            $description,
            $accountingId
        );

        $this->bookings[] = $booking;

        return $booking;
        
    }

    public function isBooked ($start, $end) {

        foreach ($this->bookings as $booking) {

            if ($booking->isBooked($start, $end)) {

                return true;

            }

        }

        return false;

    }


    public function __get ($property) {
    
        return $this->$property;
    }

    public function __set ($property, $value) {

        $this->$property = $value;

    }

    public function getPrice () {

        if (!$this->price) {
            return 0;
        } else {
            return $this->price->getAmount();
        }        

    }

    public function getDocument () {

        $data = array();
        $data['type'] = $this->type;
        $data['name'] = $this->name;
        $data['status'] = $this->status;
        if (!$this->price) {
            $data['price'] = 0;
        } else {
            $data['price'] = $this->price->getAmount();
        }
        $data['currency'] = $this->currency;

        return $data;

    }

}

Class Booking {

    private $id = NULL;
    private $userId;
    private $start;
    private $end;
    private $status = 'active';
    private $description = 'A booking';
    private $accountingId = null;

    public function disable () {

        $this->status = 'disabled';

    }

    public function getId () {

        return $this->id;

    }

    public function getDocument () {

        $data = array();
        $data['user_id'] = $this->userId;
        $data['start'] = $this->start;
        $data['end'] = $this->end;
        $data['status'] = $this->status;
        $data['description'] = $this->description;
        $data['accounting_id'] = $this->accountingId;
        return $data;

    }

    public function __construct ($start, $end, $userId, $bookingId = null, $description = 'ha?', $accountingId = null) {


        $this->userId = $userId;
        $this->setStart($start);
        $this->setEnd($end);
        $this->setDescription($description);
        $this->setAccountingId($accountingId);

        $this->id = $bookingId;

    }

    public function getDescription () 
    {

        return $this->description;

    }

    public function getStart () {

        return $this->start;

    }

    public function getEnd () {

        return $this->end;

    }

    public function getUserId () {
            
            return $this->userId;
    }

    private function setAccountingId ($accountingId) {

        $this->accountingId = $accountingId;

    }

    private function setDescription ($description) {

        $this->description = $description;

    }

    public function setStart ($start) {

        $this->start = new Carbon($start);

    }

    public function setEnd ($end) {

        $this->end = new Carbon($end);

    }

    public function getTotalMinutes () {
        $diff = $this->end->diff($this->start);
        $minutes = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
        return $minutes;
    }

    public function getTotalHours () {

        return $this->getTotalMinutes() / 60;

    }

    public function isBooked ($start, $end) {

        $startCheck = new Carbon($start);
        $endCheck = new Carbon($end);


        // Starting after bookin starts and before booking ends
        if ($startCheck >= $this->start && $endCheck < $this->end) {

            return true;
        }
        if ($endCheck > $this->start && $startCheck < $this->end) {
            return true;
        }

        return false;

    }



}