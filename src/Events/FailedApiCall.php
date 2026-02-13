<?php

namespace Jurager\Tracker\Events;

use GuzzleHttp\Exception\TransferException;
use Illuminate\Queue\SerializesModels;

class FailedApiCall
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param TransferException $exception
     */
    public function __construct(
        public TransferException $exception
    ) {
    }
}
