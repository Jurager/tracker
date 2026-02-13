<?php

namespace Jurager\Tracker\Providers\Ip;

use Jurager\Tracker\Traits\MakesApiCalls;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Support\Facades\Request;

class IpApi implements IpProviderContract
{
    use MakesApiCalls;

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
