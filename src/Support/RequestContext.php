<?php

namespace Jurager\Tracker\Support;

use Illuminate\Contracts\Container\BindingResolutionException;
use Jurager\Tracker\Factories\ProviderFactory;
use Jurager\Tracker\Contracts\ProviderContract;
use Jurager\Tracker\Factories\UserAgentParserFactory;
use Jurager\Tracker\Contracts\ParserContract;
use Illuminate\Support\Facades\Request;

class RequestContext
{
    protected ParserContract $parser;
    protected ?ProviderContract $ipProvider = null;
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
            $this->ipProvider = ProviderFactory::build(config('tracker.lookup.provider'), $this->ip);
        } catch (\Exception $e) {
            // Log error but don't fail token creation
            report($e);
        }
    }

    /**
     * Get the parser used to parse the User-Agent header.
     *
     * @return ParserContract
     */
    public function parser(): ParserContract
    {
        return $this->parser;
    }

    /**
     * Get the IP lookup result.
     *
     * @return ProviderContract|null
     */
    public function ip(): ?ProviderContract
    {
        return ($this->ipProvider?->getResult() !== null) ? $this->ipProvider : null;
    }
}
