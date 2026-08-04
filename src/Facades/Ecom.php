<?php

namespace Ecom\Payments\Facades;

use Ecom\Payments\EcomClient;
use Illuminate\Support\Facades\Facade;

class Ecom extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return EcomClient::class;
    }
}
