<?php

require __DIR__.'/../vendor/autoload.php';

use Ecom\Payments\EcomClient;
use Ecom\Payments\Exceptions\EcomApiException;
use Illuminate\Http\Client\Factory;

$http = new Factory();
$http->fake(['*' => $http->response(['data' => ['id' => 'ok']], 200)]);
$ecom = new EcomClient($http, [
    'api_token' => 'pk_test_123',
    'merchant_id' => '123456',
    'environment' => 'sandbox',
    'webhook_secret' => 'secret',
]);

assert($ecom->eApi->createCharge(['amount' => ['value' => 10, 'currency' => 'KWD']]) === ['id' => 'ok']);
$ecom->eApi->getCharge('token/one');
$ecom->eLinks->createInvoice(['language' => 'en']);
$ecom->eLinks->listInvoices(['page' => 2, 'take' => 25, 'order' => 'ASC']);
$ecom->eLinks->getInvoice('invoice/one');
$ecom->eLinks->getInvoiceByPaymentToken('payment/one');
$ecom->eLinks->deleteInvoice('invoice/one');
$ecom->eLinks->sendInvoiceReminder('invoice/one', ['sms' => true]);
$ecom->eLinks->markInvoiceAsPaid('invoice/one', ['paymentMethod' => 'CASH']);
$ecom->refunds->createRefund(['amount' => 5, 'ecomId' => 'ecom-1']);
$ecom->refunds->listRefunds(['page' => 1]);
$ecom->refunds->getRefund('refund/one');

$recorded = $http->recorded();
$requests = $recorded->map(fn (array $pair) => [$pair[0]->method(), $pair[0]->url()])->all();
assert($requests[0] === ['POST', 'https://api-sandbox.ecom.io/eapi/v1/api/charges']);
assert($requests[1] === ['GET', 'https://api-sandbox.ecom.io/eapi/v1/api/charges/token%2Fone']);
assert($requests[3] === ['GET', 'https://api-sandbox.ecom.io/elinks/v1/api/invoices?page=2&take=25&order=ASC']);
assert($requests[11] === ['GET', 'https://api-sandbox.ecom.io/transaction/v1/api/refunds/refund%2Fone']);
assert($recorded[0][0]->hasHeader('X-Ecom-Api-Token', 'pk_test_123'));
assert($recorded[0][0]->hasHeader('X-Ecom-Mid', '123456'));

$data = ['paymentStatus' => 'CAPTURED', 'amount' => '10.000', 'ignored' => null, 'ecomId' => '123'];
$expected = hash_hmac('sha256', 'amount=10.000&ecomId=123&paymentStatus=CAPTURED', 'secret');
assert($ecom->webhooks->generateSignature($data) === $expected);
assert($ecom->webhooks->verifySignature($data, $expected));
assert(! $ecom->webhooks->verifySignature($data, 'invalid'));

$failed = new Factory();
$failed->fake(['*' => $failed->response(['error' => 'UNAUTHORIZED', 'message' => ['Invalid token']], 401)]);
try {
    (new EcomClient($failed, ['api_token' => 'x', 'merchant_id' => 'y', 'environment' => 'production']))->eApi->getCharge('token');
    assert(false, 'Expected EcomApiException');
} catch (EcomApiException $exception) {
    assert($exception->status === 401);
    assert($exception->apiError === 'UNAUTHORIZED');
    assert($exception->getMessage() === 'Invalid token');
}

echo "Laravel SDK checks passed.\n";
