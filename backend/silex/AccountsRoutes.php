<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$agitator = $app['controllers_factory'];


$agitator->get('/accounts/', function (Request $request) use ($app) {

    $repo = new \Karolina\Accounting\AccountRepository();

    $accounts = $repo->getAccounts();

    $response['accounts'] = array();

    foreach ($accounts as $account) {

        $response['accounts'][] = (new \Karolina\API\v1\AccountResponse($account))->get();

    }

    $response['fields_meta'] = (new \Karolina\API\v1\AccountResponse())->getFieldsMeta();

    $response['status'] = "success";
    return $app->json($response);


})->before($adminOnly);

$agitator->get('/accounts/{accountId}/invoices/{month}/', function ($accountId, $month, Request $request) use ($app) {

    $repo = new \Karolina\Accounting\AccountRepository();
    $account = $repo->getAccountById($accountId);
    $invoiceData = $account->getInvoiceDataForMonth($month);

    $response['invoice_data'] = $invoiceData;
    $response['status'] = "success";
    return $app->json($response);

})->before($adminOnly);


$agitator->post('/accounts/{accountId}/custom_invoices/', function ($accountId, Request $request) use ($app) {

    $repo = new \Karolina\Accounting\AccountRepository();
    $account = $repo->getAccountById($accountId);

    $reference = (string) $request->get('reference');
    $description = (string) $request->get('description');
    $dueDays = (int) $request->get('due_days');
    $lines = (array) $request->get('lines');

    $invoiceData = $account->getCustomInvoiceData(
        $reference, 
        $description, 
        $dueDays, 
        $lines);

    $service = new \Karolina\Accounting\InvoicingService(getenv('invoicing_mode'));
    $service->authorize();

    $customerId = $service->createCustomer(
        $account->getCompanyIdentification(),
        $account->getBillingEmails()
    );

    $sentInvoiceData = $service->sendInvoice($customerId, $invoiceData);

    $account->addInvoice(
        $sentInvoiceData['id'],
        $sentInvoiceData['amountIncludingVat'],
        $sentInvoiceData['finalDueDate'],
        $sentInvoiceData['reference'],
        $sentInvoiceData['description'],
        $sentInvoiceData['status']
    );

    $repo->saveAccount($account);

    $response['status'] = "success";
    return $app->json($response);

})->before($adminOnly);


$agitator->post('/accounts/{accountId}/invoices/', function ($accountId, Request $request) use ($app) {

    $repo = new \Karolina\Accounting\AccountRepository();

    $account = $repo->getAccountById($accountId);

    $month = $request->get('month');

    $invoiceData = $account->getInvoiceDataForMonth($month);

    $service = new \Karolina\Accounting\InvoicingService(getenv('invoicing_mode'));
    $service->authorize();

    $customerId = $service->createCustomer(
        $account->getCompanyIdentification(),
        $account->getBillingEmails()
    );

    $sentInvoiceData = $service->sendInvoice($customerId, $invoiceData);

    $account->addInvoice(
        $sentInvoiceData['id'],
        $sentInvoiceData['amountIncludingVat'],
        $sentInvoiceData['finalDueDate'],
        $sentInvoiceData['reference'],
        $sentInvoiceData['description'],
        $sentInvoiceData['status']
    );

    $repo->saveAccount($account);

    $response['status'] = "success";
    return $app->json($response);

})->before($adminOnly);


$agitator->get('/accounts/invoiced/{month}/', function ($month, Request $request) use ($app) {

    $repo = new \Karolina\Accounting\AccountRepository();
 
    // Now return 
    $accounts = $repo->getAccounts();
    $response['accounts'] = array();

    foreach ($accounts as $account) {

        $response['accounts'][] = (new \Karolina\API\v1\AccountInvoicesResponse($account))->get($month);

    }

    $response['status'] = "success";
    return $app->json($response);


})->before($adminOnly);



$agitator->get('/accounts/updated/{month}/', function ($month, Request $request) use ($app) {

    // Start by fetching the invoices from the invoicing service
    $service = new \Karolina\Accounting\InvoicingService(getenv('invoicing_mode'));
    $service->authorize();

    $repo = new \Karolina\Accounting\AccountRepository();
    $accountsByCompanyId = $repo->getAccountsByCompanyId();

    $invoices = $service->getAllInvoices(null, null, $month);

    $invoicesBySSN = array();

    foreach ($invoices as $invoice) {

        $ssn = $invoice['customer']['ssn'];

        if (!isset($invoicesBySSN[$ssn])) {
            $invoicesBySSN[$ssn] = array();
        }

        $invoicesBySSN[$ssn][] = $invoice;

    }

    $debugInfo = array();

    foreach ($invoicesBySSN as $ssn => $invoicesOfSSN) {

        if (isset($accountsByCompanyId[$ssn])) {
            
            $account = $repo->getAccountByCompanyId($ssn);
            
            if ($account) {
                
                $debug = array();
                $debug['ssn'] = $ssn;
                $debug['id_before'] = $account->getId();
                $debug['name_before'] = $account->getName();
                $debug['document_before'] = $account->getDocument();
                
                foreach ($invoicesOfSSN as $invoice) {
                    $account->addInvoice(
                        $invoice['id'],
                        $invoice['amountIncludingVat'],
                        $invoice['finalDueDate'],
                        $invoice['reference'],
                        $invoice['description'],
                        $invoice['status']
                    );
                }
                
                $debug['name_after'] = $account->getName();
                $debug['document_after'] = $account->getDocument();
                
                $savedId = $repo->saveAccount($account);
                $debug['saved_id'] = $savedId;
                
                // Verify it actually saved
                $reloadedAccount = $repo->getAccountById($savedId);
                $debug['reloaded_name'] = $reloadedAccount->getName();
                
                $debugInfo[] = $debug;
            }
        }

    }

    // Now return 
    $accounts = $repo->getAccounts();
    $response['accounts'] = array();

    foreach ($accounts as $account) {

        $response['accounts'][] = (new \Karolina\API\v1\AccountInvoicesResponse($account))->get($month);

    }

    $response['debug_info'] = $debugInfo;
    $response['invoices'] = $invoices;
    $response['accounts_by_ssn'] = $accountsByCompanyId;
    $response['status'] = "success";
    return $app->json($response);


})->before($adminOnly);


$agitator->get('/accounts/invoices/{dateFrom}/{dateTo}/', function ($dateFrom, $dateTo, Request $request) use ($app) {

    $repo = new \Karolina\Accounting\AccountRepository();

    $accounts = $repo->getAccountsByCompanyId();

    $service = new \Karolina\Accounting\InvoicingService(getenv('invoicing_mode'));
    $service->authorize();

    // Ensure $dateFrom and $dateTo are in YYYY-MM-DD format
    $dateFrom = date('Y-m-d', strtotime($dateFrom));
    $dateTo = date('Y-m-d', strtotime($dateTo));

    $invoices = $service->getAllInvoices($dateFrom, $dateTo);

    $invoicesBySSN = array();

    foreach ($invoices as $invoice) {

        $ssn = $invoice['customer']['ssn'];

        if (!isset($invoicesBySSN[$ssn])) {
            $invoicesBySSN[$ssn] = array();
        }

        $invoicesBySSN[$ssn][] = $invoice;

    }

    foreach ($invoicesBySSN as $ssn => $invoicesOfSSN) {

        if (isset($accounts[$ssn])) {

            $account = $accounts[$ssn];
            foreach ($invoicesOfSSN as $invoice) {
                $account->addInvoice(
                    $invoice['id'],
                    $invoice['amountIncludingVat'],
                    $invoice['finalDueDate'],
                    $invoice['reference'],
                    $invoice['description'],
                    $invoice['status']
                );

            }

            $repo->saveAccount($account);

        }

    }

    $response['status'] = "success";
    return $app->json($response);


})->before($adminOnly);


$agitator->post('/accounts/import_accounts/', function (Request $request) use ($app) {

    $excelFile = $request->files->get('file')->getPathname();
    
    $source = new \Karolina\Accounting\MembersSource($excelFile);
    $accountRecords = $source->getInvoiceData();
    $repo = new \Karolina\Accounting\AccountRepository();

    foreach ($accountRecords as $accountData) {

        $account = new \Karolina\Accounting\Account();
        $account->setCompanyIdentification($accountData['Kennitala']);
        $account->setBillingEmails($accountData['Email']);
        $account->setName($accountData['Name']);
        $account->setSpaces('Hlemmur-'.$accountData['Space']);

        $officeSpace = new \Karolina\Accounting\Subscription(
            null,
            'Hlemmur office space m2',
            $accountData['Start'],
            NULL,
            $accountData['Sqm'],
            "3000"
        );

        $members = new \Karolina\Accounting\Subscription(
            null,
            'Hlemmur members',
            $accountData['Start'],
            NULL,
            $accountData['Members'],
            "6000"
        );

        $account->addSubscription($officeSpace);
        $account->addSubscription($members);
        $repo->saveAccount($account);

    }

    $response['status'] = "success";
    $response['message'] = "Accounts imported successfully.";
    $response['accountRecords'] = $accountRecords;

    return $app->json($response);



    foreach ($invoices as $invoice) {
        $repo = new \Karolina\Accounting\AccountRepository();
        $account = new \Karolina\Accounting\Account();

        $account->setCompanyIdentification($invoice['kennitala']);
        $account->setBillingEmails($invoice['email']);
        $account->setName($invoice['name']);

        $officeSpace = new \Karolina\Accounting\Subscription(
            null,
            'Hlemmur Office space m2',
            $invoice['start_date'],
            NULL,
            $invoice['square_meters'],
            "3000"
        );

        $members = new \Karolina\Accounting\Subscription(
            null,
            'Hlemmur Members',
            $invoice['start_date'],
            NULL,
            $invoice['members'],
            "6000"
        );

        $account->addSubscription($officeSpace);
        $account->addSubscription($members);
        $repo->saveAccount($account);
    }



})->before($adminOnly);


$agitator->post('/accounts/{accountId}/subscriptions/', function ($accountId, Request $request) use ($app) {

    $repo = new \Karolina\Accounting\AccountRepository();
    $account = $repo->getAccountById($accountId);

    $subscription = new \Karolina\Accounting\Subscription(
        $accountId,
        $request->get('item'),
        $request->get('start'),
        $request->get('end'),
        $request->get('units'),
        $request->get('unit_price')
    );

    $account->addSubscription($subscription);
    $repo->saveAccount($account);

    $response['status'] = "success";
    return $app->json($response);

})->before($adminOnly);


$agitator->post('/accounts/', function (Request $request) use ($app) {

    $repo = new \Karolina\Accounting\AccountRepository();

    $account = new \Karolina\Accounting\Account(
        $request->get('currency')
    );

    $account->setName($request->get('name'));
    $account->setSpaces($request->get('spaces'));    
    $account->setBillingEmails($request->get('billing_emails'));
    $account->setCompanyIdentification($request->get('company_identification'));

    $accountId = $repo->saveAccount($account);

    $account->setId($accountId);

    if (isset($request->get('subscriptions')[0])) {

        foreach ($request->get('subscriptions') as $subscription) {

            $account->addSubscription(
                new \Karolina\Accounting\Subscription(
                    $accountId,
                    $subscription['item'],
                    $subscription['start'],
                    $subscription['end'],
                    $subscription['units'],
                    $subscription['unit_price']
                )
            );

        }

    }


    $repo->saveAccount($account);

    $response['status'] = "success";
    return $app->json($response);


})->before($adminOnly);

$agitator->delete('/accounts/{accountId}/', function ($accountId, Request $request) use ($app) {

    $repo = new \Karolina\Accounting\AccountRepository();

    $account = $repo->getAccountById($accountId);

    $account->disable();
    $repo->saveAccount($account);
    $response['status'] = "success";
    return $app->json($response);


})->before($adminOnly);

$agitator->delete('/accounts/{accountId}/subscriptions/{subscriptionId}/', function ($accountId, $subscriptionId, Request $request) use ($app) {

    $repo = new \Karolina\Accounting\AccountRepository();

    $account = $repo->getAccountById($accountId);

    $account->removeSubscription($subscriptionId);
    $repo->saveAccount($account);

    $response['status'] = "success";
    return $app->json($response);


})->before($adminOnly);


$agitator->patch('/accounts/{accountId}/subscriptions/{subscriptionId}/', function ($accountId, $subscriptionId, Request $request) use ($app) {

    $repo = new \Karolina\Accounting\AccountRepository();
    $account = $repo->getAccountById($accountId);

    $patch['item'] = "setSubscriptionItem";
    $patch['unit_price'] = "setSubscriptionUnitPrice";
    $patch['units'] = "setSubscriptionUnits";
    $patch['start'] = "setSubscriptionStart";
    $patch['end'] = "setSubscriptionEnd";


    foreach ($patch as $key => $method) {

        if ($request->get($key)) {

            $account->$method($subscriptionId, $request->get($key));

        }

    }

    $repo->saveAccount($account);


    $response['status'] = "success";
    return $app->json($response);


})->before($adminOnly);

$agitator->patch('/accounts/{accountId}/', function ($accountId, Request $request) use ($app) {

    $repo = new \Karolina\Accounting\AccountRepository();
    $account = $repo->getAccountById($accountId);

    $patch['currency'] = "setCurrency";
    $patch['name'] = "setName";
    $patch['spaces'] = "setSpaces";
    $patch['billing_emails'] = "setBillingEmails";
    $patch['company_identification'] = "setCompanyIdentification";

    foreach ($patch as $key => $method) {

        if ($request->get($key)) {

            $account->$method($request->get($key));

        }

    }

    $repo->saveAccount($account);
    $response['status'] = "success";
    return $app->json($response);


})->before($adminOnly);



return $agitator;
