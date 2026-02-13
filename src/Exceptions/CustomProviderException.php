<?php

namespace Jurager\Tracker\Exceptions;

use Exception;
use Throwable;

class CustomProviderException extends Exception
{
    public function __construct(
        string $message = 'Choose a valid IP address lookup provider. The class must implement the Jurager\Tracker\Interfaces\IpProvider interface.',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
