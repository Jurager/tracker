<?php

namespace Jurager\Tracker\Exceptions;

use Exception;
use Throwable;

class ProviderException extends Exception
{
    public function __construct(
        string $message = 'Choose a supported IP address lookup provider.',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
