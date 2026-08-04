<?php

namespace Ecom\Payments;

class EApi
{
    public function __construct(private readonly HttpClient $http) {}

    public function createCharge(array $request): array
    {
        return $this->http->request('POST', 'eapi', '/v1/api/charges', $request);
    }

    public function getCharge(string $paymentToken): array
    {
        return $this->http->request('GET', 'eapi', '/v1/api/charges/'.rawurlencode($paymentToken));
    }
}
