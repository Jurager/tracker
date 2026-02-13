<?php

namespace Jurager\Tracker\Providers;

use GuzzleHttp\Exception\GuzzleException;
use Jurager\Tracker\Contracts\ProviderContract;
use Jurager\Tracker\Traits\MakesHttpCalls;

/**
 * Abstract base class for IP providers that use HTTP APIs.
 *
 * Extend this class to create your own IP provider with automatic HTTP handling.
 * You only need to implement the getRequest() method and the data extraction methods.
 *
 * Example:
 * ```php
 * class MyCustomProvider extends AbstractProvider
 * {
 *     public function getRequest(): GuzzleRequest
 *     {
 *         return new GuzzleRequest('GET', 'https://api.example.com/lookup/' . $this->ip);
 *     }
 *
 *     public function getCountry(): ?string
 *     {
 *         return $this->result?->get('country');
 *     }
 *
 *     // ... implement other methods
 * }
 * ```
 */
abstract class AbstractProviderContract implements ProviderContract
{
    use MakesHttpCalls;

    protected readonly string $ip;

    /**
     * Initialize the IP provider.
     *
     * Note: Child classes that override __construct must call parent::__construct() BEFORE
     * calling the MakesHttpCalls trait constructor to ensure $ip is set before making API calls.
     *
     * @param string|null $ip The IP address to lookup. If not provided, will use current request's IP.
     * @throws GuzzleException
    */
    public function __construct(?string $ip = null)
    {
        $this->ip = $ip ?? \Illuminate\Support\Facades\Request::ip() ?? '127.0.0.1';
        $this->initializeHttpClient();
    }

    /**
     * Initialize the HTTP client and make the API call.
     * This is called after $ip is set.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function initializeHttpClient(): void
    {
        $timeout = (float) config('tracker.lookup.timeout', 1.0);

        $this->httpClient = new \GuzzleHttp\Client([
            'connect_timeout' => $timeout,
            'timeout' => $timeout,
        ]);

        $this->result = $this->makeApiCall();
    }

    /**
     * Get the country name from the API response.
     *
     * @return string|null
     */
    abstract public function getCountry(): ?string;

    /**
     * Get the region name from the API response.
     *
     * @return string|null
     */
    abstract public function getRegion(): ?string;

    /**
     * Get the city name from the API response.
     *
     * @return string|null
     */
    abstract public function getCity(): ?string;
}
