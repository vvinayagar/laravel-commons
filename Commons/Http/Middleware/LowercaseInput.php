<?php

namespace Commons\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

class LowercaseInput extends TransformsRequest
{
    protected function transform($key, $value)
    {
        return in_array($key, $this->attributes) && is_string($value) ? strtolower($value) : $value;
    }
}
