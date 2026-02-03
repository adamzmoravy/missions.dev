<?php

namespace Karolina\API\v1;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use Carbon\Carbon;

Class AccountResponse extends Response {

    private $account;


    public function __construct ($account = null) {

        $this->account = $account;

    }

    public function get () {

        $currencies = new ISOCurrencies();
        $numberFormatter = new \NumberFormatter('nl_NL', \NumberFormatter::DECIMAL);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, $currencies);

    
        $doc = $this->account->getDocument();
        $r['account_id'] = (string) $this->account->getId();
        $r['name'] = (string) $doc['name'];
        $r['spaces'] = (string) $doc['spaces'];
        $r['company_identification'] = (string) $doc['company_identification'];
        $r['billing_emails'] = (string) $doc['billing_emails'];
        $r['currency'] = (string) $doc['currency'];
        $r['invoices'] = $this->account->getInvoices();

        $r['invoiced_total'] = $this->account->getTotalInvoicedAmount();
        $r['invoiced_this_month'] = $this->account->getTotalInvoicedAmount(Carbon::now()->format('Y-m'));

        $r['total_charges'] = (int) $this->account->totalChargeUntil('2022-11-30')->getAmount();

        $r['total_ongoing_subscriptions'] = (float) $this->account->totalOngoingSubscriptions()->getAmount();
        

        $subscriptions = $this->account->getSubscriptions();
        $r['subscriptions_count'] = (int) count($subscriptions);

        $r['subscriptions'] = array();
        $r['subscriptions']['records'] = array();
        $r['subscriptions']['fields_meta'] =  (new SubscriptionResponse(null))->getFieldsMeta();

        foreach ($subscriptions as $subscription) {

            $r['subscriptions']['records'][] = (new SubscriptionResponse($subscription))->get();

        }

        return $r;

    }

    public function getFieldsMeta () {

        return array(
            'account_id' => array(
                'type' => 'string',
                'label' => 'ID',
                'description' => 'The account id',
                'editable' => false
            ),
            'name' => array(
                'type' => 'string',
                'label' => 'Name',
                'description' => 'The name of the account',
                'editable' => true,
                'required' => true
            ),
            'spaces' => array(
                'type' => 'string',
                'label' => 'Spaces',
                'description' => 'The spaces the account has',
                'editable' => true,
                'required' => false
            ),
            'company_identification' => array(
                'type' => 'kennitala',
                'label' => 'Kennitala',
                'description' => 'The company identification number',
                'editable' => true,
                'required' => true            
            ),
            'billing_emails' => array(
                'type' => 'string',
                'label' => 'Billing emails',
                'description' => 'Seperate with commas',
                'editable' => true,
                'required' => true
            ),
            'total_ongoing_subscriptions' => array(
                'type' => 'number',
                'label' => 'Total subscribed',
                'editable' => false
            ),
            'invoiced_total' => array(
                'type' => 'number',
                'label' => 'Invoiced all time',
                'editable' => false
            ),
            'subscriptions' => array(
                'type' => 'Object',
                'label' => 'Subscriptions',
                'editable' => false,
                'component' => 'subscriptions'
            )

        );


    }

}