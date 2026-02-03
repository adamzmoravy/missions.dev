<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$agitator = $app['controllers_factory'];


$agitator->patch('/resources/{resourceId}/', function ($resourceId, Request $request) use ($app) {

    $repo = new \Karolina\Accounting\ResourceRepository();
    $resource = $repo->getResourceById($resourceId);

    $patch['name'] = "setName";
    $patch['price'] = "setPrice";
    $patch['image'] = "setImage";
    $patch['description'] = "setDescription";
    $patch['price'] = "setPrice";

    foreach ($patch as $key => $method) {

        if ($request->get($key)) {

            $resource->$method($request->get($key));

        }

    }

    $repo->saveResource($resource);
    $response['status'] = "success";
    return $app->json($response);


})->before($adminOnly);

$agitator->get('/resources/{resourceId}/', function ($resourceId, Request $request) use ($app) {

    $repo = new \Karolina\Accounting\ResourceRepository();

    $resource = $repo->getResourceById($resourceId);
    $response['resource'] = (new \Karolina\API\v1\ResourceResponse($resource))->get();

    $response['status'] = "success";
    return $app->json($response);

});


$agitator->delete('/resources/{resourceId}/', function ($resourceId, Request $request) use ($app) {

    $repo = new \Karolina\Accounting\ResourceRepository();

    $resource = $repo->getResourceById($resourceId);

    $resource->disable();
    $repo->saveResource($resource);

    $response['status'] = "success";
    return $app->json($response);

})->before($adminOnly);


$agitator->get('/resources/type/{type}/', function ($type, Request $request) use ($app) {

    $repo = new \Karolina\Accounting\ResourceRepository();

    $resources = $repo->getResourcesByType($type);

    $response['resources'] = array();

    foreach ($resources as $resource) {

        $response['resources'][] = (new \Karolina\API\v1\ResourceResponse($resource))->get();

    }

    $response['fields_meta'] = (new \Karolina\API\v1\ResourceResponse())->getFieldsMeta();


    $response['status'] = "success";
    return $app->json($response);

});

$agitator->delete('/resources/{resourceId}/bookings/{bookingId}/', function ($resourceId, $bookingId, Request $request) use ($app) {

    $repo = new \Karolina\Accounting\ResourceRepository();
    $resource = $repo->getResourceById($resourceId);
    $booking = $resource->getBookingById($bookingId);

    if (
        !$app['currentUser']->isAdmin() &&
        $app['currentUser']->getId() !== $booking->getUserId()
    ) {
        throw new \Karolina\Exception('Not allowed to delete this booking.');
    }

    $resource->removeBooking($bookingId);
    $repo->saveResource($resource);

    $response['status'] = "success";
    return $app->json($response);

})->before($authRequired);

$agitator->post('/resources/{resourceId}/bookings/', function ($resourceId, Request $request) use ($app) {

    $repo = new \Karolina\Accounting\ResourceRepository();

    $resource = $repo->getResourceById($resourceId);

    $booking = $resource->makeBookingByMinute(
        $request->get('start'),
        $request->get('end'),
        $app['currentUser']->getId(),
        $request->get('description'),
        $request->get('accounting_id')
    );

    $repo->saveResource($resource);

    $account_id = $request->get('accounting_id');
    
    if ($resource->getPrice() != 0 && !$resource->isFreeForAccount($account_id)) {


        $currentUser = $app['currentUser'];
        $nameOfBooker = $currentUser->getName();
        $emailOfBooker = $currentUser->getEmail();

        $reference = 'room-booking';
        $description = 'Booking of a space. Requested by '.$currentUser->getName().' ('.$emailOfBooker.'). ';
        $description .= 'Reason for booking: '.$booking->getDescription();

        $dueDays = 3;

        $line['description'] = "Hourly rent of space: ".$resource->getName();
        $line['units'] = $booking->getTotalHours();
        $line['amount'] = $resource->getPrice();
        $line['discount_percentage'] = 0;
        $lines[] = $line;

        // Create an new account if no exist?
        $account = new \Karolina\Accounting\Account();

        $invoiceData = $account->getCustomInvoiceData(
            $reference, 
            $description, 
            $dueDays, 
            $lines);

        $service = new \Karolina\Accounting\InvoicingService(getenv('invoicing_mode'));
        $service->authorize();
    
        $customerId = $service->createCustomer(
            $account_id,
            $emailOfBooker
        );
    
        $sentInvoiceData = $service->sendInvoice($customerId, $invoiceData);
            

    }
    
    $response['status'] = "success";
    return $app->json($response);

})->before($authRequired);

$agitator->post('/resources/', function (Request $request) use ($app) {

    $repo = new \Karolina\Accounting\ResourceRepository();

    $resource = new \Karolina\Accounting\Resource();

    $resource->setName($request->get('name'));
    $resource->setPrice($request->get('price'));
    $resource->setType($request->get('type'));
    $resource->setDescription($request->get('description'));
    $resource->setImage($request->get('image'));

    $repo->saveResource($resource);

    $response['status'] = "success";
    return $app->json($response);

})->before($adminOnly);




return $agitator;
