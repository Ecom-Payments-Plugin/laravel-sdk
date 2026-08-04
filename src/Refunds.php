<?php

namespace Ecom\Payments;

class Refunds
{
    public function __construct(private readonly HttpClient $http) {}

    public function createRefund(array $request): array
    {
        return $this->http->request('POST', 'transaction', '/v1/api/refunds', $request);
    }

    public function listRefunds(array $query = []): array
    {
        return $this->http->request('GET', 'transaction', '/v1/api/refunds', query: $query);
    }

    public function getRefund(string $refundId): array
    {
        return $this->http->request('GET', 'transaction', '/v1/api/refunds/'.rawurlencode($refundId));
    }
}
