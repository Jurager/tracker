<?php

namespace Jurager\Tracker\Factories;

use Jurager\Tracker\Contracts\IpProvider;
use Jurager\Tracker\Exceptions\CustomProviderException;
use Jurager\Tracker\Exceptions\ProviderException;
use Jurager\Tracker\Providers\Ip2LocationLite;
use Jurager\Tracker\Providers\IpApi;
use Illuminate\Support\Facades\App;

class IpProviderFactory
{
    /**
     * Build a new IP provider.
     *
     * @param string|false|null $name
     * @param string|null $ip Optional IP address to lookup
     * @return IpProvider|null
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public static function build(string|false|null $name, ?string $ip = null): ?IpProvider
    {
        if (!self::ipLookupEnabled()) {
            return null;
        }

        $customProviders = config('tracker.lookup.custom_providers', []);

        // Check for custom provider
        if (is_array($customProviders) && array_key_exists($name, $customProviders)) {

            $providerClass = $customProviders[$name];

            if (!in_array(IpProvider::class, class_implements($providerClass) ?: [], true)) {
                throw new CustomProviderException(
                    "Custom IP provider {$providerClass} must implement " . IpProvider::class
                );
            }

            return new $providerClass($ip);
        }

        // Use officially supported provider
        return match ($name) {
            'ip2location-lite' => new Ip2LocationLite($ip),
            'ip-api' => new IpApi($ip),
            false,
            null => null,
            default => throw new ProviderException("Unsupported IP provider: {$name}"),
        };
    }

    /**
     * Check if the IP lookup feature is enabled.
     *
     * @return bool
     */
    public static function ipLookupEnabled(): bool
    {
        $provider = config('tracker.lookup.provider');
        $environments = config('tracker.lookup.environments', []);

        return $provider !== false
            && $provider !== null
            && App::environment($environments);
    }
}
