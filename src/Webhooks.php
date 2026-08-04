<?php

namespace Ecom\Payments;

class Webhooks
{
    public function __construct(private readonly ?string $secret = null) {}

    public function generateSignature(array $data, ?string $secret = null): string
    {
        $secret ??= $this->secret;
        if (! is_string($secret) || $secret === '') {
            throw new \InvalidArgumentException('Webhook secret is required');
        }
        $data = array_filter($data, static fn (mixed $value): bool => $value !== null);
        uksort($data, 'strcasecmp');
        $payload = implode('&', array_map(
            static fn (string $key, mixed $value): string => $key.'='.(is_bool($value) ? ($value ? 'true' : 'false') : (string) $value),
            array_keys($data),
            $data,
        ));

        return hash_hmac('sha256', $payload, $secret);
    }

    public function verifySignature(array $data, string $signature, ?string $secret = null): bool
    {
        return preg_match('/^[a-f\d]{64}$/i', $signature) === 1
            && hash_equals($this->generateSignature($data, $secret), strtolower($signature));
    }
}
