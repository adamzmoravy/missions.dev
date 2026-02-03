<?php

namespace Karolina\Accounting;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client as Client;

Class InvoicingService {

    private $config;

    private $headers = [
        'Content-Type' => 'application/json',
        'Api-Version' => 'alpha'
      ];

    private $bearerToken;

    public function __construct($mode) {

        $dev['endpoint'] = "https://api.test.payday.is";
        $dev['clientId'] = getenv('payday_client_id_development');
        $dev['clientSecret'] = getenv('payday_secret_development');
        $dev['mode'] = 'development';

        $prod['endpoint'] = "https://api.payday.is";
        $prod['clientId'] = getenv('payday_client_id');
        $prod['clientSecret'] = getenv('payday_secret');
        $prod['mode'] = 'production';

        if ($mode == 'development') {
            $this->config = $dev;
        } else if ($mode == 'production') {
            $this->config = $prod;
        }

    }

    public function authorize () {

        $client = new Client();

        $body = [
            "clientId" => $this->config['clientId'],
            "clientSecret" => $this->config['clientSecret']
          ];
          
          $request = new Request('POST', $this->config['endpoint'].'/auth/token', $this->headers, json_encode($body));
          $res = $client->sendAsync($request)->wait();
          $response = json_decode($res->getBody(), true);
          
          $this->bearerToken = $response['accessToken'];
          $this->headers['Authorization'] = 'Bearer '.$this->bearerToken;
          
    }

    public function createCustomer ($kennitala, $email) {

        if ($this->config['mode'] == 'development') {
            $email = 'testcustomer'.time().'@example.com';
        }

        $client = new Client();

        $body = '{
          "ssn": "'.$kennitala.'",
          "email": "'.$email.'",
          "language": "en"
        }';

        $request = new Request('POST', $this->config['endpoint'].'/customers', $this->headers, $body);
        $res = $client->sendAsync($request)->wait();
        
        $clientId = json_decode($res->getBody(), true)['id'];
        
        return $clientId;

    }

    public function customersInvoices () {

      $allInvoices = $this->getAllInvoices();

      $customers = array();

      foreach ($allInvoices['invoices'] as $invoice) {

        $ssn = (string) $invoice['customer']['ssn'];

        if (!isset($customers[$ssn])) {
          $customers[$ssn] = array();
          $customers[$ssn]['num_invoices'] = 0;
          $customers[$ssn]['num_invoices_credit'] = 0;
          $customers[$ssn]['sum_invoices'] = 0;
          $customers[$ssn]['months'] = array();
        }
      

        if ($invoice['status'] != 'CREDIT') {
          $customers[$ssn]['num_invoices']++;

          if ($invoice['status'] != 'CANCELLED') {
            if ($invoice['reference'] == '' && $invoice['reference'] < 42) {
              $invoice['reference'] = '2022-09'; // Fix cause we didn't use reference before
            }
            $customers[$ssn]['months'][] = $invoice['reference'];  
          }

        }
        $customers[$ssn]['sum_invoices'] =+ $invoice['amountIncludingVat'];

        if ($invoice['status'] == 'CREDIT') {
          $customers[$ssn]['num_invoices_credit']++;          
        }

      }

      return $customers;

    }

    public function getAllInvoices ($dateFrom = null, $dateTo = null, $query = null) {

        $client = new Client();

        $invoices = array();

        $done = false;
        $page = 1;

        while (!$done) {

            $dateString = '';
            if ($dateFrom) {
                $dateString .= '&dateFrom='.$dateFrom;
            }
            if ($dateTo) {
                $dateString .= '&dateTo='.$dateTo;
            }

            $queryString = '';
            if ($query) {
                $queryString = '&query='.urlencode($query);
            }

            $request = new Request('GET', $this->config['endpoint'].'/invoices?limit=100&page='.$page.$dateString.$queryString, $this->headers);
            $res = $client->sendAsync($request)->wait();
            $response = json_decode($res->getBody(), true);

            $invoices = array_merge($invoices, $response['invoices']);
            
            $done = true;

            $page++;

            if ($response['page'] < $response['pages']) {
                $done = false;
            }           

        }

        return $invoices;

    }

    public function sendInvoice ($clientId, $body) {
        
        $client = new Client();

        $body['customer']['id'] = $clientId;

        $request = new Request('POST', $this->config['endpoint'].'/invoices', $this->headers, json_encode($body));
        $res = $client->sendAsync($request)->wait();
        return json_decode($res->getBody(), true);
  
    }

}