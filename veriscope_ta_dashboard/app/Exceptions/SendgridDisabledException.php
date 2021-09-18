<?php

namespace App\Exceptions;

use Exception;

class SendgridDisabledException extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
