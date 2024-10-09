<?php

namespace Commons\Validations;


use Illuminate\Validation\Rule;

class Validations
{
    const EMAIL = 'email|max:50';
    const PASSWORD = 'min:8|max:50';
    const PIN = 'numeric|digits:4';
    const NAME = 'min:5|max:250';
    const DESCRIPTION = 'min:5|max:10000';
    const PHONE_NUMBER = 'digits_between:7,14';
}
