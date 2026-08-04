# Ecom Payments Laravel SDK

Laravel SDK for E_API charges, E_LINKS invoices, refunds, and webhook signature
verification. It supports Laravel 10 through 13 and uses Laravel's native HTTP
client.

## Requirements

- PHP 8.1 or later
- Laravel 10, 11, 12, or 13
- Ecom API Token and Merchant ID

## Installation

```bash
composer require ecom-payments/laravel-sdk
```

Laravel discovers the service provider and facade automatically. Publish the
configuration file if it needs to be customized:

```bash
php artisan vendor:publish --tag=ecom-config
```

## Configuration

Add the Ecom credentials to `.env`:

```dotenv
ECOM_API_TOKEN=pk_test_xxxxxx
ECOM_MERCHANT_ID=123456
ECOM_ENVIRONMENT=sandbox
ECOM_WEBHOOK_SECRET=your_webhook_secret
```

Use `sandbox` while testing and `production` for live payments.

## E_API charges

Use dependency injection:

```php
use Ecom\Payments\EcomClient;

public function checkout(EcomClient $ecom)
{
    $charge = $ecom->eApi->createCharge([
        'amount' => ['value' => 10, 'currency' => 'KWD'],
        'options' => ['mode' => 'INDIRECT'],
        'urls' => [
            'successUrl' => route('payments.ecom.success'),
            'errorUrl' => route('payments.ecom.failure'),
        ],
        'references' => ['merchantReference' => 'order-123'],
        'customer' => [
            'fullName' => 'Ali',
            'email' => 'ali@example.com',
        ],
        'language' => 'en',
    ]);

    return redirect()->away($charge['paymentUrl']);
}
```

Retrieve the result using the payment token returned to the success URL:

```php
$charge = $ecom->eApi->getCharge($request->string('paymentToken'));

if (($charge['status'] ?? $charge['paymentStatus'] ?? null) === 'CAPTURED') {
    // Mark the local order as paid.
}
```

The facade is also available:

```php
use Ecom\Payments\Facades\Ecom;

$charge = Ecom::eApi()->getCharge($paymentToken);
```

## E_LINKS invoices

```php
$invoice = $ecom->eLinks->createInvoice([
    'amount' => ['value' => 25, 'currency' => 'KWD'],
    'customer' => [
        'fullName' => 'Ali',
        'phoneCode' => '+965',
        'phoneNumber' => '66778899',
    ],
    'notification' => ['email' => true, 'sms' => true],
    'language' => 'en',
]);

$invoices = $ecom->eLinks->listInvoices([
    'page' => 1,
    'take' => 10,
    'order' => 'DESC',
]);

$invoice = $ecom->eLinks->getInvoice($invoiceId);
$invoice = $ecom->eLinks->getInvoiceByPaymentToken($paymentToken);
$ecom->eLinks->sendInvoiceReminder($invoiceId, ['email' => true]);
$ecom->eLinks->markInvoiceAsPaid($invoiceId, ['paymentMethod' => 'CASH']);
$ecom->eLinks->deleteInvoice($invoiceId);
```

## Refunds

```php
$refund = $ecom->refunds->createRefund([
    'amount' => 5,
    'ecomId' => $charge['id'],
    'merchantReference' => 'refund-order-123',
]);

$refunds = $ecom->refunds->listRefunds(['page' => 1, 'take' => 10]);
$refund = $ecom->refunds->getRefund($refundId);
```

## Webhooks

Verify the `X-Webhook-Signature` header before processing an event:

```php
use Ecom\Payments\EcomClient;
use Illuminate\Http\Request;

// Add this route to routes/api.php so it is not protected by CSRF middleware.
Route::post('/webhooks/ecom', function (Request $request, EcomClient $ecom) {
    $data = $request->input('data', []);
    $signature = (string) $request->header('X-Webhook-Signature');

    abort_unless($ecom->webhooks->verifySignature($data, $signature), 401);

    return response()->noContent();
});
```

Use the event's `eventType` to distinguish `TRANSACTION_STATUS_CHANGED` from
`REFUND_STATUS_CHANGED`.

## Errors

Non-successful or malformed responses throw `EcomApiException`:

```php
use Ecom\Payments\Exceptions\EcomApiException;

try {
    $charge = $ecom->eApi->getCharge($paymentToken);
} catch (EcomApiException $exception) {
    report($exception);

    logger()->error('Ecom request failed', [
        'status' => $exception->status,
        'api_error' => $exception->apiError,
        'body' => $exception->body,
    ]);
}
```

## Development

```bash
composer install
composer validate --strict
composer test
```
