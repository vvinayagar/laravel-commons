<?php

namespace Commons\Helpers;


use Illuminate\Support\Str;

class TokenHelper
{
    public static function generateToken() {
        return Str::random(50);
    }
}