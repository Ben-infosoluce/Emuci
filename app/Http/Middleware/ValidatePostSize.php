<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\ValidatePostSize as Middleware;

class ValidatePostSize extends Middleware
{
    protected $maxPostSize = 20480; // 20 Mo (en kilo-octets)
}
