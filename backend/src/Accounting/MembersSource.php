<?php
namespace Karolina\Accounting;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


Class MembersSource {

    private $excelFile;

    public function __construct ($excelFile) {

        $this->excelFile = $excelFile;

    }


    public function getInvoiceData () {

        $sheet = $this->getSheet();
        $invoices = $this->makeInvoicesArray($sheet);
        return $invoices;

    }

    private function getSheet () {

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($this->excelFile);
        $rows = $spreadsheet->getActiveSheet()->toArray();
        $header = $rows[0];
        /*
        echo "<pre>";
        print_r($header);
        echo "</pre>";
        */

        return $rows;
        
    }

    private function makeInvoicesArray ($rows) {

        $header = $rows[0];
        array_shift($rows); // remove header row

        $invoices = array();

        foreach ($rows as $row) {
            $account = array();
            foreach ($header as $key => $column) {
                $account[$column] = $row[$key];
            }
            $invoices[] = $account;
        }

        return $invoices;
    }
        

    private function makeInvoicesArrayOUT ($rows) {

        $invoices = array();        

        foreach ($rows as $row) {

            $kennitala = str_replace('-', '', $row[2]);
            $email = trim($row[3]);
            $square_meters = $row[8];
            $start_date = $row[9];
            $discount = $row[10];
            $invoiceYesOrNo = strtolower($row[0]);
            $members = $row[4];
            $name = $row[1];

            $problems = array();

            $invoice = array();
            if ($invoiceYesOrNo == 'oct') {
                $kennitalaCheck = strtolower($this->kennitala($kennitala));
                if ($kennitalaCheck == 'gild kennitala') {
                } else {
                    $problems[] = 'Kennitala not valid'.' ('.$kennitala.') '.$kennitalaCheck;
                }
                $invoice['kennitala'] = $kennitala;

                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                } else {
                    $problems[] = 'Email not valid'.' ('.$email.')';
                }
                $invoice['email'] = $email;
        
                if (filter_var($members, FILTER_VALIDATE_INT) && ((int) $members > 0)) {
                    $invoice['members'] = (int) $members;
                } else {
                    $problems[] = 'Members is not valid'.' ('.$members.') for '.$kennitala;
                }
                $invoice['members'] = (int) $members;

                if (filter_var($square_meters, FILTER_VALIDATE_FLOAT) && ($square_meters > 0)) {
                    $invoice['square_meters'] = $square_meters;
                } else {
                    $problems[] = 'Square meters '.' ('.$square_meters.') for '.$kennitala;
                }
                $invoice['square_meters'] = $square_meters;

                $invoice['start_date'] = $start_date;
                if ($discount == "") {
                    $invoice['discount'] = "0";                   
                } else {
                    $invoice['discount'] = $discount;
                }

                $invoice['problems'] = $problems;

                $invoice['name'] = $name;

                $invoice['payment'] = ($invoice['members'] * 5000) + ($invoice['square_meters'] * 2500);

                $invoices[] = $invoice;
            }
        
        
        }

        return $invoices;
        
    }


    private function kennitala($tala) {
        // ath lengd 
        if(strlen($tala) != 10) {
            return "Ogild kennitala 1"; 
        }
        
    
        /* Array fyrir modulus 11 reikning */
        $weight = array( 2, 3, 4, 5, 6, 7,
                 2, 3); 
        
        // First 8 to calculate the 9th number
        $first_8 = mb_substr($tala, 0,8);
        
        // First 9 that we were given
        $first_9 = mb_substr($tala, 0, 9);
        
        // Turn it around.. 
        $reverse = strrev( $first_8 );
        
        for ( $i = 0, $sum = 0; $i < strlen( $reverse ); $i++ ) {
            $sum += substr( $reverse, $i, 1 ) * $weight[ $i ];
        }
    
        $reminder = $sum % 11;
        
        switch( $reminder ) {
            case 0: 
                $result = $first_8 . 0;
                break;
            
            case 1: 
                $result = "n/a";
                break;
            
            default: 
                $check_digit = 11 - $reminder;
                $result = $first_8 . $check_digit;
                break;
        } 
    
        if( $result != $first_9 ) {
            return "Ogild Kennitala 2";
        }  
    
        return "Gild kennitala";
    
        // The last number is the century
        $century = mb_substr($tala, 9, 10); // The last number
        $year = mb_substr($tala, -6, -4); // Year of birth 2 digit format
        $d = DateTime::createFromFormat('y', $year); // Change 2 digit format to 4 digit format
        $year = $d->format('Y'); // So we can use the 4 digit format
        
        // 19 century
        if($century == 8) {
            if(preg_match('/18../', $year)) {
                return "Gild kennitala 3"; 
            } else {
                return "Ogild Kennitala 4"; 
            }
        }
    
        // 20 century
        elseif($century == 9) {
            if(preg_match('/19../', $year)) {
                return "Gild Kennitala"; 
            } else {
                return "Ogild Kennitala 5"; 
            }
        }
    
        // 21 Century 
        elseif($century == 0) {
            if(preg_match('/20../', $year)) {
                return "Gild Kennitala"; 
            } else {
                return "Ogild Kennitala 6"; 
            }
        }  
        
    }
    

}

