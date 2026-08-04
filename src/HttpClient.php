<?php

namespace Ecom\Payments;

use Ecom\Payments\Exceptions\EcomApiException;
use Illuminate\Http\Client\Factory;

class HttpClient
{
    private const HOSTS = [
        'sandbox' => 'https://api-sandbox.ecom.io',
        'production' => 'https://api-live.ecom.io',
    ];

    public function __construct(
        private readonly Factory $http,
        private readonly string $apiToken,
        private readonly string $merchantId,
        private readonly string $environment,
    ) {}

    public function request(
        string $method,
        string $service,
        string $path,
        array $body = [],
        array $query = [],
        bool $unwrap = true,
    ): mixed {
        $url = self::HOSTS[$this->environment].'/'.$service.$path;
        $options = array_filter([
            'json' => $body ?: null,
            'query' => $query ?: null,
        ]);
        $response = $this->http
            ->withHeaders([
                'X-Ecom-Api-Token' => $this->apiToken,
                'X-Ecom-Mid' => $this->merchantId,
            ])
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->send($method, $url, $options);

        $parsed = $response->json();
        if ($response->failed()) {
            $message = is_array($parsed) ? ($parsed['message'] ?? null) : null;
            throw new EcomApiException(
                $response->status(),
                $parsed ?? $response->body(),
                is_array($parsed) && isset($parsed['error']) ? (string) $parsed['error'] : null,
                is_array($message) ? implode(', ', $message) : $message,
            );
        }
        if (! $unwrap) {
            return null;
        }
        if (! is_array($parsed) || ! array_key_exists('data', $parsed)) {
            throw new EcomApiException($response->status(), $parsed ?? $response->body(), message: 'Ecom API response is missing data');
        }

        return $parsed['data'];
    }
}
