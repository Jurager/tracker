<?php

namespace Jurager\Tracker\Providers\Ip;

use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Support\Facades\Request;

use Jurager\Tracker\Providers\AbstractIpProvider;

class IpApi extends AbstractIpProvider
{

    /**
     * Get the Guzzle request.
     *
     * @return GuzzleRequest
     */
    public function getRequest(): GuzzleRequest
    {
        $ip = Request::ip() ?? '127.0.0.1';
        $locale = config('app.locale', 'en');

        return new GuzzleRequest(
            'GET',
            "http://ip-api.com/json/{$ip}?fields=25&lang={$locale}"
        );
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
