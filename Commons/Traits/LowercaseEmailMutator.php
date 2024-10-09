<?php

namespace Commons\Traits;

trait LowercaseEmailMutator
{
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }
}