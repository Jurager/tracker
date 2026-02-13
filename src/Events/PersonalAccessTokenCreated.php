<?php

namespace Jurager\Tracker\Events;

use Jurager\Tracker\Models\PersonalAccessToken;
use Jurager\Tracker\Support\RequestContext;
use Illuminate\Queue\SerializesModels;

class PersonalAccessTokenCreated
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param PersonalAccessToken $personalAccessToken The newly created PersonalAccessToken
     * @param RequestContext $context Information about the request (user agent, ip address, etc.)
     */
    public function __construct(
        public PersonalAccessToken $personalAccessToken,
        public RequestContext $context
    ) {
    }
}
