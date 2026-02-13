<?php

namespace Jurager\Tracker\Support;

use Jurager\Tracker\Providers\IpProviderFactory;
use Jurager\Tracker\Providers\IpProviderContract;
use Jurager\Tracker\Providers\UserAgentParserFactory;
use Jurager\Tracker\Providers\UserAgentParserContract;
use Illuminate\Support\Facades\Request;

class RequestContext
{
    protected UserAgentParserContract $parser;
    protected ?IpProviderContract $ipProvider = null;
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
     * @return UserAgentParserContract
     */
    public function parser(): UserAgentParserContract
    {
        return $this->parser;
    }

    /**
     * Get the IP lookup result.
     *
     * @return IpProviderContract|null
     */
    public function ip(): ?IpProviderContract
    {
        if ($this->ipProvider && $this->ipProvider->getResult()) {
            return $this->ipProvider;
        }

        return null;
    }
}
