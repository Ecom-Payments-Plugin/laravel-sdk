<?php

namespace Ecom\Payments;

use Illuminate\Http\Client\Factory;
use InvalidArgumentException;

class EcomClient
{
    public readonly EApi $eApi;
    public readonly ELinks $eLinks;
    public readonly Refunds $refunds;
    public readonly Webhooks $webhooks;

    public function __construct(Factory $http, array $config)
    {
        $apiToken = (string) ($config['api_token'] ?? '');
        $merchantId = (string) ($config['merchant_id'] ?? '');
        $environment = (string) ($config['environment'] ?? '');
        if ($apiToken === '' || $merchantId === '') {
            throw new InvalidArgumentException('Ecom API token and merchant ID are required');
        }
        if (! in_array($environment, ['sandbox', 'production'], true)) {
            throw new InvalidArgumentException('Ecom environment must be sandbox or production');
        }

        $client = new HttpClient($http, $apiToken, $merchantId, $environment);
        $this->eApi = new EApi($client);
        $this->eLinks = new ELinks($client);
        $this->refunds = new Refunds($client);
        $this->webhooks = new Webhooks($config['webhook_secret'] ?? null);
    }

    public function eApi(): EApi
    {
        return $this->eApi;
    }

    public function eLinks(): ELinks
    {
        return $this->eLinks;
    }

    public function refunds(): Refunds
    {
        return $this->refunds;
    }

    public function webhooks(): Webhooks
    {
        return $this->webhooks;
    }
}
