<?php

namespace Jurager\Tracker\Providers;

use GuzzleHttp\Psr7\Request as GuzzleRequest;

class IpApi extends AbstractProvider
{
    /**
     * Get the Guzzle request.
     *
     * @return GuzzleRequest
     */
    public function getRequest(): GuzzleRequest
    {
        $locale = config('app.locale', 'en');
        $url = sprintf('http://ip-api.com/json/%s?fields=25&lang=%s', $this->ip, $locale);

        return new GuzzleRequest('GET', $url);
    }

    /**
     * Get the country name.
     *
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->result?->get('country');
    }

    /**
     * Get the region name.
     *
     * @return string|null
     */
    public function getRegion(): ?string
    {
        return $this->result?->get('regionName');
    }

    /**
     * Get the city name.
     *
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->result?->get('city');
    }
}
