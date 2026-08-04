<?php

namespace Ecom\Payments;

class ELinks
{
    public function __construct(private readonly HttpClient $http) {}

    public function createInvoice(array $request): array
    {
        return $this->http->request('POST', 'elinks', '/v1/api/invoices', $request);
    }

    public function listInvoices(array $query = []): array
    {
        return $this->http->request('GET', 'elinks', '/v1/api/invoices', query: $query);
    }

    public function getInvoice(string $invoiceId): array
    {
        return $this->http->request('GET', 'elinks', '/v1/api/invoices/'.rawurlencode($invoiceId));
    }

    public function getInvoiceByPaymentToken(string $paymentToken): array
    {
        return $this->http->request('GET', 'elinks', '/v1/api/invoices/payment-token/'.rawurlencode($paymentToken));
    }

    public function deleteInvoice(string $invoiceId): void
    {
        $this->http->request('DELETE', 'elinks', '/v1/api/invoices/'.rawurlencode($invoiceId), unwrap: false);
    }

    public function sendInvoiceReminder(string $invoiceId, array $request): void
    {
        $this->http->request('POST', 'elinks', '/v1/api/invoices/'.rawurlencode($invoiceId).'/reminder', $request, unwrap: false);
    }

    public function markInvoiceAsPaid(string $invoiceId, array $request): void
    {
        $this->http->request('PATCH', 'elinks', '/v1/api/invoices/'.rawurlencode($invoiceId).'/mark-as-paid', $request, unwrap: false);
    }
}
