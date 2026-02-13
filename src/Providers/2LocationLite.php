<?php

namespace Jurager\Tracker\Providers;

use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Jurager\Tracker\Contracts\ProviderContract;

class LocationLite implements ProviderContract
{
    protected readonly string $ip;
    protected ?object $result = null;

    /**
     * Ip2LocationLite constructor.
     *
     * @param string|null $ip The IP address to lookup. If not provided, will use current request's IP.
     */
    public function __construct(?string $ip = null)
    {
        $this->ip = $ip ?? Request::ip() ?? '127.0.0.1';

        // Skip lookup for localhost or invalid IP
        if ($this->ip === '127.0.0.1' || !filter_var($this->ip, FILTER_VALIDATE_IP)) {
            $this->result = null;
            return;
        }

        $isIpv6 = filter_var($this->ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);

        $table = $isIpv6
            ? config('tracker.lookup.ip2location.ipv6_table', 'ip2location_db3_ipv6')
            : config('tracker.lookup.ip2location.ipv4_table', 'ip2location_db3');

        // Use appropriate IP conversion function based on IP version
        $ipFunction = $isIpv6 ? 'INET6_ATON' : 'INET_ATON';

        $this->result = DB::table($table)
            ->whereRaw("{$ipFunction}(?) <= ip_to", [$this->ip])
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
