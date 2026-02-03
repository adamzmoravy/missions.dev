<?php 

namespace Karolina\Accounting;
use Carbon\Carbon;
use Money\Currency;
use Money\Currencies\ISOCurrencies;
use Money\Money;
use Money\Parser\DecimalMoneyParser;

Class Subscription {

    private $id = NULL;
    private $start;
    private $end;
    private $item;
    private $accountId;
    private $units;
    private $unitPrice;
    private $currency;
    private $status = 'active';

    public function __construct ($accountId = NULL, $item, $start, $end, $units, $unitPrice, $currency = 'ISK') {

        $this->accountId = $accountId;
        $this->item = $item;
        $this->setStart($start);
        $this->setEnd($end);
        $this->setUnits($units);
        $this->currency = new Currency($currency);
        $this->setUnitPrice($unitPrice);

    }

    public function setItem ($item) {
        $this->item = $item;
    }

    public function disable () {

        $this->status = 'disabled';

    }

    public function setAccountId ($accountId) {

        $this->accountId = $accountId;

    }

    public function getUnits () {
            
        return $this->units;
        
    }

    public function getUnitPrice () {

        // TODO: Here I have added 10% increase. This is just temporary, and should be a setting
        // Changed this back again
        return $this->unitPrice;
        
    }

    public function getAccountId () {
            
        return $this->accountId;
    
    }

    public function getItem () {

        return $this->item;

    }

    public function getId () {

        return $this->id;

    }

    public function totalChargeUntil ($date = null) {

        if ($date == null) {
            $date = Carbon::now();
        } else {
            $date = new Carbon($date);
        }

        if ($this->end < $date && $this->end != null) {
            $date = $this->end;
        }

        $months = $this->start->startOfDay()->floatDiffInMonths($date->endOfDay());

        $monthsRound = round($months, 2);

        return $this->getTotalMoney()->multiply($monthsRound);

    }

    public function totalChargeUntilMonthly ($date = null) {

        if ($date == null) {
            $date = Carbon::now();
        } else {
            $date = new Carbon($date);
        }

        if ($this->end < $date && $this->end != null) {
            $date = $this->end;
        }

        $months = $this->start->startOfDay()->diffInMonths($date->endOfMonth());

        $monthsRound = round($months, 2);

        return $this->getTotalMoney()->multiply($monthsRound);

    }

    public function totalChargeUntilString ($date) {

        return $this->totalChargeUntil($date)->getAmount();

    }

    public function howMuchChargeForMonth ($month = null) {

        $fraction = $this->chargeForMonthFraction($month);

        $charge = $this->getTotalMoney()->multiply($fraction);

        return $charge;
    }

    public function howMuchChargeForMonthString ($month = null) {

        return $this->howMuchChargeForMonth($month)->getAmount();
    }

    public function chargeForMonthFraction ($month = null) {

        if ($month == null) {
            $month = Carbon::now()->format('Y-m');
        } else {
            $month = Carbon::createFromFormat('Y-m', $month);
        }

        if ($this->start == null or $this->start < $month->startOfMonth()->copy()) {
            $start = $month->startOfMonth()->copy();
        } else {
            $start = $this->start;
        }

        if ($this->end == null or $this->end > $month->endOfMonth()->copy()) {
            $end = $month->endOfMonth()->copy();
        } else {
            $end = $this->end;
        }

        if ($start > $month->endOfMonth()->copy()) {
            return 0;
        }

        if ($end < $month->startOfMonth()->copy()) {
            return 0;
        }

        $daysInMonth = $month->daysInMonth;
        $daysInSubscription = $start->startOfDay()->floatDiffInDays($end->endOfDay());

        // 10 days in subscription / 30 days in month
        $fraction = $daysInSubscription / $daysInMonth;

        return round($fraction, 2);

    }    

    public function chargeForMonthFraction2 ($month = null) {

        if ($month == null) {
            $month = Carbon::now()->format('Y-m');
        } else {
            $month = Carbon::createFromFormat('Y-m', $month);
        }

        // Subscription started after the month
        if ($this->start > $month->endOfMonth()) {
            return 0;
        } 

        // Subscription ends before the month
        if ($this->end < $month->startOfMonth()) {
            return 0;
        } 

        // Subscription started before and ends after
        if ($this->start <= $month->startOfMonth() && $this->end->endOfDay() >= $month->endOfMonth()) {
            return 1;
        }

        // Subscription started before the month starts
        if ($this->start <= $month->startOfMonth()) {
            $start = $month->startOfMonth();
        } else { // Subscription started after the month starts
            $start = $this->start;
        }

        if ($this->start == null) {
            $start = $month->startOfMonth();
        }

        if ($this->end == null) {
            $end = $month->endOfMonth();
        } 

        // Subscription ends after the month ends
        if ($this->end == null or $this->end->endOfDay() >= $month->endOfMonth()) {
            $end = $month->endOfMonth();
        } else { // Subscription ends before the month ends
            $end = $this->end;
        }
/**
        // Subscription ends before end of month
        if ($this->end != null && $this->end->endOfDay() < $month->endOfMonth()) {
            $months =  $this->start->startOfDay()->floatDiffInMonths($this->end->endOfDay());
            return round($months, 2);

        }
 */

        $daysInMonth = $month->daysInMonth;
        $daysInSubscription = $start->startOfDay()->floatDiffInDays($end->endOfDay());
        // 10 days in subscription / 30 days in month
        $fraction = $daysInSubscription / $daysInMonth;

        return round($fraction, 2);

    }

    public function setUnitPrice ($unitPrice) {

        $currencies = new ISOCurrencies();
        $moneyParser = new DecimalMoneyParser($currencies);
        $this->unitPrice = $moneyParser->parse($unitPrice, $this->currency);
        
    }


    public function setUnits ($units) {

        if ($units < 1) {
            throw new \Exception("Units must be at least 1");
        }

        $this->units = $units;

    }

    public function setStart($start) {

        $this->start = new Carbon($start);

    }

    public function setEnd($end = null) {

            if ($end == null or $end == "") {
            $this->end = null;
            return true;
        } 

        $end = new Carbon($end);

        if ($end > $this->start) {
            $this->end = $end;
        } else {
            throw new \Exception("End date must be after start date");
        }

    }

    public function getCurrentMonths ($from = null) {

        if (!$from) {
            $from = Carbon::now();
        } else {
            $from = new Carbon($from);
        }
        $months = $from->floatDiffInMonths($this->start);

        return $months;
    }

    public function getTotalMoney ($from = null) {

        return $this->unitPrice->multiply($this->units);

    }

    public function hasEnded () {

        if ($this->end == null) {
            return false;
        }

        if (Carbon::now()->endOfDay() > $this->end->endOfDay()) {
            return true;
        }

        return false;

    }

    public function hasStarted () {

        if ($this->start->startOfDay() < Carbon::now()->startOfDay()) {
            return true;
        }

        return false;

    }

    public function isOnGoing () {
            
        if ($this->hasStarted() && !$this->hasEnded()) {
            return true;
        }

        return false;
    
    }

    public function getTotalString () {

        return $this->getTotalMoney()->getAmount();
    }


    public function getDocument () {

        $document = array();
        $document['item'] = $this->item;
        $document['start'] = $this->start->format('Y-m-d');

        if ($this->end) {
            $document['end'] = $this->end->format('Y-m-d');
        } else 
        {
            $document['end'] = null;
        }

        $document['units'] = $this->units;
        $document['unit_price'] = $this->unitPrice->getAmount();
        $document['currency'] = $this->currency->getCode();
        $document['status'] = $this->status;

        return $document;

    }



    public function __set ($name, $value) {

        $this->$name = $value;

    }

}