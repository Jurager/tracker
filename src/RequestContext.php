<?php

namespace Jurager\Tracker;

use Jurager\Tracker\Factories\IpProviderFactory;
use Jurager\Tracker\Factories\ParserFactory;
use Jurager\Tracker\Interfaces\IpProvider;
use Jurager\Tracker\Interfaces\UserAgentParser;
use Illuminate\Support\Facades\Request;

class RequestContext
{
    /**
     * @var UserAgentParser $parser
     */
    protected $parser;

    /**
     * @var IpProvider $ipProvider
     */
    protected $ipProvider = null;

    /**
     * @var string $userAgent
     */
    public $userAgent;

    /**
     * @var string|null $ip
     */
    public $ip;

    /**
     * RequestContext constructor.
     *
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function __construct()
    {
        // Initialize the parser
        $this->parser = ParserFactory::build(config('tracker.parser'));

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
    public function parser()
    {
        return $this->parser;
    }

    /**
     * Get the IP lookup result.
     *
     * @return IpProvider
     */
    public function ip()
    {
        if ($this->ipProvider && $this->ipProvider->getResult()) {
            return $this->ipProvider;
        }

        return null;
    }
}
