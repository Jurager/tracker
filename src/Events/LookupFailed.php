<?php

namespace Jurager\Tracker\Events;

use GuzzleHttp\Exception\TransferException;
use Illuminate\Queue\SerializesModels;

class LookupFailed
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param TransferException $exception The exception that caused the lookup failure
     * @param string|null $ip The IP address that failed to lookup
     */
    public function __construct(
        public TransferException $exception,
        public ?string $ip = null
    ) {
    }
}
