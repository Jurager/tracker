<?php

namespace Jurager\Tracker\Providers;

use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Collection;

interface IpProviderContract
{
    /**
     * Get the Guzzle request.
     *
     * @return Request
     */
    public function getRequest(): Request;

    /**
     * Get the country name.
     *
     * @return string|null
     */
    public function getCountry(): ?string;

    /**
     * Get the region name.
     *
     * @return string|null
     */
    public function getRegion(): ?string;

    /**
     * Get the city name.
     *
     * @return string|null
     */
    public function getCity(): ?string;

    /**
     * Get the result of the API call.
     *
     * @return Collection|null
     */
    public function getResult(): ?Collection;
}
