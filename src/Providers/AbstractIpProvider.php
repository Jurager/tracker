<?php

namespace Jurager\Tracker\Providers;

use Jurager\Tracker\Traits\MakesHttpCalls;

/**
 * Abstract base class for IP providers that use HTTP APIs.
 *
 * Extend this class to create your own IP provider with automatic HTTP handling.
 * You only need to implement the getRequest() method and the data extraction methods.
 *
 * Example:
 * ```php
 * class MyCustomProvider extends AbstractIpProvider
 * {
 *     public function getRequest(): GuzzleRequest
 *     {
 *         return new GuzzleRequest('GET', 'https://api.example.com/lookup/' . Request::ip());
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
abstract class AbstractIpProvider implements IpProviderContract
{
    use MakesHttpCalls;

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
