<?php

namespace Jurager\Tracker\IpProviders;

use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

use Jurager\Tracker\Contracts\IpProvider;

class Ip2LocationLite implements IpProvider
{
    protected ?object $result = null;

    public function __construct()
    {
        $ip = Request::ip();

        if (!$ip) {
            return;
        }

        $isIpv6 = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);

        $table = $isIpv6
            ? config('tracker.lookup.ip2location.ipv6_table', 'ip2location_db3_ipv6')
            : config('tracker.lookup.ip2location.ipv4_table', 'ip2location_db3');

        $this->result = DB::table($table)
            ->whereRaw('INET_ATON(?) <= ip_to', [$ip])
            ->first();
    }

    /**
     * Get the Guzzle request.
     * Note: This provider uses database instead of HTTP requests.
     *
     * @return GuzzleRequest
     */
    public function getRequest(): GuzzleRequest
    {
        // This provider doesn't use HTTP requests
        return new GuzzleRequest('GET', 'about:blank');
    }

    /**
     * Get the country name.
     *
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->result?->country_name ?? null;
    }

    /**
     * Get the region name.
     *
     * @return string|null
     */
    public function getRegion(): ?string
    {
        return $this->result?->region_name ?? null;
    }

    /**
     * Get the city name.
     *
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->result?->city_name ?? null;
    }

    /**
     * Get the result of the query.
     *
     * @return Collection|null
     */
    public function getResult(): ?Collection
    {
        return $this->result ? collect((array) $this->result) : null;
    }
}
