<?php

namespace Karolina\API\v1;

use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;

Class SubscriptionResponse {

    private $subscription;

    public function __construct ($subscription) {

        $this->subscription = $subscription;

    }

    public function get () {

        $r = array();

        $currencies = new ISOCurrencies();
        $numberFormatter = new \NumberFormatter('nl_NL', \NumberFormatter::DECIMAL);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, $currencies);

        $doc = $this->subscription->getDocument();
        $r = array(
            'subscription_id' => (string) $this->subscription->getId(),
            'account_id' => (string) $this->subscription->getAccountId(),
            'item' => (string) $doc['item'],
            'start' => (string) $doc['start'],
            'end' => (string) $doc['end'],
            'units' => $doc['units'],
            'unit_price' => $doc['unit_price'],
            'total_monthly' =>  $moneyFormatter->format($this->subscription->getTotalMoney()),
            'currency' => (string) $doc['currency']
        );    


        return $r;

    }

    public function getFieldsMeta () {

        return array(
            'subscription_id' => array(
                'type' => 'text',
                'label' => 'ID',
                'description' => 'The subscription id',
                'editable' => false
            ),
            'item' => array(
                'type' => 'string',
                'label' => 'Item',
                'description' => 'The service being subscribed to',
                'editable' => true,
                'required' => true,
                'suggestions' => array(
                    'Coworking' => 'Coworking',
                    'Members' => 'Members',
                    'Office space m2' => 'Office space m2',
                    'Storage space' => 'Storage space',
                    'Gym membership' => 'Gym membership',
                    'Hlemmur members' => 'Hlemmur members',
                    'Hlemmur office space m2' => 'Hlemmur office space m2',
                    'Hlemmur open space' => 'Hlemmur open space'
                ), 
                'suggestions' => array(
                    array(
                        'value' => 'Coworking',
                        'label' => 'Coworking',
                    ),
                    array(
                        'value' => 'Members',
                        'label' => 'Members',
                    ),
                    array(
                        'value' => 'Office space m2',
                        'label' => 'Office space m2',
                    ),
                    array(
                        'value' => 'Hlemmur members',
                        'label' => 'Hlemmur members',
                    ),
                    array(
                        'value' => 'Hlemmur office space m2',
                        'label' => 'Hlemmur office space m2',
                    ),
                    array(
                        'value' => 'Hlemmur open space',
                        'label' => 'Hlemmur open space',
                    ),
                    array(
                        'value' => 'Storage space',
                        'label' => 'Storage space',
                    ),
                    array(
                        'value' => 'Gym membership',
                        'label' => 'Gym membership',
                    )
                )
            ),
            'unit_price' => array(
                'type' => 'string',
                'label' => 'Unit price',
                'description' => 'The price of each unit',
                'editable' => true,
                'required' => true,
                'suggestions' => array(
                    array(
                        'value' => '12000',
                        'label' => 'Coworking',
                    ),
                    array(
                        'value' => '3000',
                        'label' => 'Office space m2',
                    ),
                    array(
                        'value' => '2400',
                        'label' => 'Storage space',
                    ),
                    array(
                        'value' => '6000',
                        'label' => 'Members',
                    ),
                    array(
                        'value' => '1100',
                        'label' => 'Gym membership',
                    ),
                    array(
                        'value' => '6000',
                        'label' => 'Hlemmur members',
                    ),
                    array(
                        'value' => '3000',
                        'label' => 'Hlemmur office space m2',
                    ),
                    array(
                        'value' => '2750',
                        'label' => 'Hlemmur open space m2',
                    )
                )
            ),
            'units' => array(
                'type' => 'number',
                'label' => 'Units',
                'description' => 'The number of units',
                'editable' => true,
                'required' => true,
                'suggestions' => array(
                    array(
                        'value' => '1',
                        'label' => '1',
                    )
                )

            ),
            'total_monthly' => array(
                'type' => 'string',
                'label' => 'Per month',
                'description' => 'Total for this subsription per month',
                'editable' => false,
                'required' => false
            ),
            'start' => array(
                'type' => 'date',
                'label' => 'Start',
                'description' => 'The start date',
                'editable' => true,
                'required' => true            
            ),
            'end' => array(
                'type' => 'date',
                'label' => 'End',
                'description' => 'The end date',
                'editable' => true,
                'required' => false
            )
        );

    }

}