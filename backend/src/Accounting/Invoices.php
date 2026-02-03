<?php


Class Invoices {

    private $invoicingService;

    public function __construct() {

        $this->invoicingService = new InvoiceService();
    }

}