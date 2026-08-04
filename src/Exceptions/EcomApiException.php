<?php

namespace Ecom\Payments\Exceptions;

use RuntimeException;

class EcomApiException extends RuntimeException
{
    public function __construct(
        public readonly int $status,
        public readonly mixed $body,
        public readonly ?string $apiError = null,
        ?string $message = null,
    ) {
        parent::__construct($message ?: "Ecom API request failed with status {$status}");
    }
}
