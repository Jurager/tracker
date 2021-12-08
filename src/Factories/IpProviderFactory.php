<?php

namespace Jurager\Tracker\Factories;

use Jurager\Tracker\Exceptions\CustomIpProviderException;
use Jurager\Tracker\Exceptions\IpProviderException;
use Jurager\Tracker\Interfaces\IpProvider;
use Jurager\Tracker\IpProviders\Ip2LocationLite;
use Jurager\Tracker\IpProviders\IpApi;
use Illuminate\Support\Facades\App;

class IpProviderFactory
{
    /**
     * Build a new IP provider.
     *
     * @param string $name
     * @return IpApi|object|void
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public static function build($name)
    {
        if (self::ipLookupEnabled()) {
            $customProviders = config('tracker.lookup.custom_providers');

            if ($customProviders && array_key_exists($name, $customProviders)) {

                // Use of a custom IP address lookup provider

                if (!in_array(IpProvider::class, class_implements($customProviders[$name]))) {

                    // The custom IP provider class doesn't
                    // implement the required interface

                    throw new CustomIpProviderException;
                }

                return new $customProviders[$name];

            } else {

                // Use of an officially supported IP address lookup provider

                switch ($name) {
                    case 'ip2location-lite':
                        return new Ip2LocationLite;
                    case 'ip-api':
                        return new IpApi;
                    default:
                        throw new IpProviderException;
                }
            }
        }
    }

    /**
     * Check if the IP lookup feature is enabled.
     *
     * @return bool
     */
    public static function ipLookupEnabled()
    {
        return config('tracker.lookup.provider') &&
            App::environment(config('tracker.lookup.environments'));
    }
}
