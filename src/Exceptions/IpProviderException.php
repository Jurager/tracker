<?php

namespace Jurager\Tracker\Exceptions;

use Exception;

class IpProviderException extends Exception
{
    public function __construct()
    {
        parent::__construct('Choose a supported IP address lookup provider.');
    }
}
