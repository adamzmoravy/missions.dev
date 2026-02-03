<?php

namespace Karolina\Accounting;
use Money\Currency;
use Money\Money;
use Carbon\Carbon;

Class Account {

    private $id = NULL;
    private $subscriptions = array();
    private $currency;
    private $companyIdentification = "";
    private $billingEmails;
    private $name;
    private $status = 'active';
    private $data;
    private $spaces = '';

    public function __construct ($currency = 'ISK') {

            if ($currency == "") {
                $currency = 'ISK';
            }

            $this->currency = new Currency($currency);

    }

    public function getBillingEmails () {

        return $this->billingEmails;

    }

    public function getName () {

        return $this->name;

    }

    public function getCompanyIdentification () {

        return $this->companyIdentification;

    }


    public function addInvoice (
            $invoiceId,
            $amount,
            $due,
            $reference,
            $description,
            $status
        )    
    {

        $this->data['invoices'][$invoiceId] = array(
            'amount' => $amount,
            'due' => $due,
            'reference' => $reference,
            'description' => $description,
            'status' => $status
        );

    }

    public function getInvoices () {

        if (isset($this->data['invoices'])) {
            return $this->data['invoices'];
        }

        return [];

    }

    public function getInvoiceDataForMonth ($month) {

        $monthName = Carbon::createFromFormat('Y-m-d', $month.'-01')->format('F Y');
        $description = "Monthly costs for ".$monthName;

        $body = [
            "description" => $description,
            "invoiceDate" => Carbon::now()->format('Y-m-d'),
            "dueDate" => Carbon::now()->addDays(7)->format('Y-m-d'),
            "finalDueDate" => Carbon::now()->addDays(7)->format('Y-m-d'),
            "reference" => $month,
            "currencyCode" => $this->currency->getCode(),
            "createClaim" => true,
            "sendEmail" => true,
            "lines" => [
            ]
        ];

        foreach ($this->getSubscriptionsActiveForMonth($month) as $key => $subscription) {

            $line = array();

            $line['position'] = $key + 1;
            $line['description'] = $subscription->getItem();
            $line['quantity'] = $subscription->getUnits();
            $line['unitPriceExcludingVat'] = (int) $subscription->getUnitPrice()->getAmount();
            $line['vatPercentage'] = 0;
            $discountFloat = 1 - $subscription->chargeForMonthFraction($month);
            $line['discountPercentage'] = $discountFloat * 100;
            $body['lines'][] = $line;

        }

        return $body;

    }

    public function getCustomInvoiceData (
        $reference = 'custom',
        $description = 'Invoice',
        $dueDays = 14,
        $lines = []
    ) {


        $body = [
            "description" => $description,
            "invoiceDate" => Carbon::now()->format('Y-m-d'),
            "dueDate" => Carbon::now()->addDays($dueDays)->format('Y-m-d'),
            "finalDueDate" => Carbon::now()->addDays($dueDays)->format('Y-m-d'),
            "reference" => $reference,
            "currencyCode" => $this->currency->getCode(),
            "createClaim" => true,
            "sendEmail" => true,
            "lines" => [
            ]
        ];

        foreach ($lines as $key => $line) {

            $line['position'] = $key + 1;
            $line['description'] = $line['description'];
            $line['quantity'] = $line['units'];
            $line['unitPriceExcludingVat'] = $line['amount'];
            $line['vatPercentage'] = 0;
            $line['discountPercentage'] = $line['discount_percentage'];
            $body['lines'][] = $line;

        }

        return $body;
        
    }

    public function getTotalInvoicedAmount ($reference = null) {
            
            $total = 0;
    
            if (isset($this->data['invoices'])) {
                foreach ($this->data['invoices'] as $invoice) {
    
                    if ($reference == null or $invoice['reference'] == $reference) {
        
                        $total += $invoice['amount'];
        
                    }
    
                }
    
            }    

            return $total;
    
    }

    public function getTotalPaidAmount ($reference = null) {
            
            $total = 0;
    
            if (isset($this->data['invoices'])) {
                foreach ($this->data['invoices'] as $invoice) {
    
                    if ($reference == null or $invoice['reference'] == $reference) {
        
                        if ($invoice['status'] == 'PAID') {
                            $total += $invoice['amount'];
                        }
        
                    }
    
                }
    
            }    

            return $total;
    
    }

    public function setSubscriptionItem ($subscriptionId, $item) {

        $this->subscriptions[
            $this->getSubscriptionIndex($subscriptionId)
            ]->setItem($item);
    }


    public function setSubscriptionUnitPrice ($subscriptionId, $price) {

        $this->subscriptions[
            $this->getSubscriptionIndex($subscriptionId)
            ]->setUnitPrice($price);
    }



    public function setSubscriptionUnits ($subscriptionId, $units) {

        $this->subscriptions[
            $this->getSubscriptionIndex($subscriptionId)
            ]->setUnits($units);
    }


    public function setSubscriptionStart ($subscriptionId, $date) {

        $this->subscriptions[
            $this->getSubscriptionIndex($subscriptionId)
            ]->setStart($date);
    }


    public function setSubscriptionEnd ($subscriptionId, $date) {

        $this->subscriptions[
            $this->getSubscriptionIndex($subscriptionId)
            ]->setEnd($date);
    }    

    private function getSubscriptionIndex ($subscriptionId) {

        $index = 0;

        foreach ($this->subscriptions as $subscription) {

            if ($subscription->getId() == $subscriptionId) {

                return $index;

            }

            $index++;

        }

        return false;

    }

    public function disable () {

        $this->status = 'disabled';

    }

    public function setCurrency ($currency) {

        $this->currency = new Currency($currency);

    }

    public function setName ($name) {

        $this->name = (string) $name;

    }

    public function setSpaces ($spaces) {

        $this->spaces = (string) $spaces;

    }


    public function totalChargeForMonth ($month) {

        $total = new Money(0, $this->currency);

        foreach ($this->subscriptions as $subscription) {

            $total = $total->add($subscription->howMuchChargeForMonth($month));

        }

        return $total;
    }

    public function getSubscriptionsActiveForMonth ($month) {

        $subscriptions = array();

        foreach ($this->subscriptions as $subscription) {

            if ($subscription->chargeForMonthFraction($month) != 0) {

                $subscriptions[] = $subscription;

            }

        }

        return $subscriptions;


    }

    public function getOngoingSubscriptions () {
            
            $subscriptions = array();
    
            foreach ($this->subscriptions as $subscription) {
    
                if ($subscription->isOngoing()) {
    
                    $subscriptions[] = $subscription;
    
                }
    
            }
    
            return $subscriptions;
    
    }

    public function totalOngoingSubscriptions () {

        $total = new Money(0, $this->currency);

        foreach ($this->subscriptions as $subscription) {

            if ($subscription->isOnGoing()) {

                $total = $total->add($subscription->getTotalMoney());

            }

        }

        return $total;

    }

    public function removeSubscription ($subscriptionId) {

        $newSubscriptions = array();

        foreach ($this->subscriptions as $subscription) {

            if ($subscription->getId() == $subscriptionId) {

                $subscription->disable();
                return true;

            }

        }

        throw new \Exception("Subscription not found");

    }

    public function setCompanyIdentification ($companyIdentification) {

        $this->companyIdentification = (string) $companyIdentification;

    }

    public function setBillingEmails ($billingEmails) {

        $emailsArray = explode(',', $billingEmails);

        // check if valid email addresses in $emailsArray

        foreach ($emailsArray as $email) {

            if (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {

                throw new \Karolina\Exception("Invalid email address: ".$email);

            }

        }

        $this->billingEmails = (string) $billingEmails;

    }

    public function setId ($id) {

        $this->id = (string) $id;

    }

    public function getId () {

        return $this->id;

    }

    public function totalChargeUntil ($date = null) {

        $total = new Money(0, $this->currency);

        foreach ($this->subscriptions as $subscription) {

            $total = $total->add($subscription->totalChargeUntil($date));

        }

        return $total;

    } 

    public function addSubscription (Subscription $subscription) {

        $subscription->setAccountId($this->id);

        $this->subscriptions[] = $subscription;
    }


    public function getSubscriptions () {

        return $this->subscriptions;

    }

    public function __set ($name, $value) {

        $this->$name = $value;

    }

    public function getDocument () {

        $document = array();

        $document['name'] = $this->name;
        $document['company_identification'] = $this->companyIdentification;
        $document['currency'] = $this->currency->getCode();
        $document['billing_emails'] = $this->billingEmails;
        $document['status'] = $this->status;
        $document['data'] = $this->data;
        $document['spaces'] = $this->spaces;

        return $document;

    }

}
    
