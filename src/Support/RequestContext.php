<?php

namespace Jurager\Tracker\Support;

use Illuminate\Contracts\Container\BindingResolutionException;
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
     * Get or create a singleton instance for the current request.
     *
     * @return static
     * @throws BindingResolutionException
    */
    public static function current(): static
    {
        return app()->make(static::class);
    }

    /**
     * RequestContext constructor.
     *
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function __construct()
    {
        $this->userAgent = Request::userAgent();
        $this->ip = Request::ip();

        try {
            // Initialize the parser with the current User-Agent
            $this->parser = UserAgentParserFactory::build(config('tracker.parser'), $this->userAgent);

            // Initialize the IP provider with the current IP
            $this->ipProvider = IpProviderFactory::build(config('tracker.lookup.provider'), $this->ip);
        } catch (\Exception $e) {
            // Log error but don't fail token creation
            report($e);
        }
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
