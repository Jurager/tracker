<?php

namespace Jurager\Tracker\Exceptions;

use Exception;

class CustomIpProviderException extends Exception
{
    public function __construct()
    {
        parent::__construct('Choose a valid IP address lookup provider. The class must implement the Jurager\Tracker\Interfaces\IpProvider interface.');
    }
}
