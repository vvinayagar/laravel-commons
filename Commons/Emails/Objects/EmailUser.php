<?php


namespace Commons\Emails\Objects;


class EmailUser
{
    public $email;
    public $name;

    public function __construct($email, $name = null)
    {
        $this->email = $email;
        $this->name = $name;
    }
}
