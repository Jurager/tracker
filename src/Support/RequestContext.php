<?php

namespace Jurager\Tracker\Support;

use Jurager\Tracker\Factories\IpProviderFactory;
use Jurager\Tracker\Contracts\IpProvider;
use Jurager\Tracker\Factories\UserAgentParserFactory;
use Jurager\Tracker\Contracts\UserAgentParser;
use Illuminate\Support\Facades\Request;

class RequestContext
{
    protected UserAgentParser $parser;
    protected ?IpProvider $ipProvider = null;
    public ?string $userAgent;
    public ?string $ip;

    /**
     * RequestContext constructor.
     *
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function __construct()
    {
        // Initialize the parser
        $this->parser = UserAgentParserFactory::build(config('tracker.parser'));

        // Initialize the IP provider
        $this->ipProvider = IpProviderFactory::build(config('tracker.lookup.provider'));

        $this->userAgent = Request::userAgent();
        $this->ip = Request::ip();
    }

    /**
     * Get the parser used to parse the User-Agent header.
     *
     * @return UserAgentParser
     */
    public function parser(): UserAgentParser
    {
        return $this->parser;
    }

    /**
     * Get the IP lookup result.
     *
     * @return IpProvider|null
     */
    public function ip(): ?IpProvider
    {
        if ($this->ipProvider && $this->ipProvider->getResult()) {
            return $this->ipProvider;
        }

        return null;
    }
}
