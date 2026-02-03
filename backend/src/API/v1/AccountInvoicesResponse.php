<?php

namespace Karolina\API\v1;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use Carbon\Carbon;

Class AccountInvoicesResponse extends Response {

    private $account;


    public function __construct ($account) {

        $this->account = $account;

    }

    public function get ($month) {

        $currencies = new ISOCurrencies();
        $numberFormatter = new \NumberFormatter('nl_NL', \NumberFormatter::DECIMAL);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, $currencies);

    
        $doc = $this->account->getDocument();
        $r['account_id'] = (string) $this->account->getId();
        $r['name'] = (string) $doc['name'];
        $r['company_identification'] = (string) $doc['company_identification'];

        $r['invoiced_total'] = $this->account->getTotalInvoicedAmount();
        $r['invoiced_period'] = $this->account->getTotalInvoicedAmount($month);
        $r['paid_period'] = $this->account->getTotalPaidAmount($month);
        

        $r['owed_period'] = (int) $this->account->totalChargeForMonth($month)->getAmount();
        $r['balance_period'] = $r['invoiced_period'] - $r['owed_period'] ;


        return $r;

    }


}