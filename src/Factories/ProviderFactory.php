<?php

namespace Jurager\Tracker\Factories;

use Jurager\Tracker\Contracts\ProviderContract;
use Jurager\Tracker\Exceptions\CustomProviderException;
use Jurager\Tracker\Exceptions\ProviderException;
use Jurager\Tracker\Providers\LocationLite;
use Jurager\Tracker\Providers\IpApi;
use Illuminate\Support\Facades\App;

class ProviderFactory
{
    /**
     * Build a new IP provider.
     *
     * @param string|false|null $name
     * @param string|null $ip Optional IP address to lookup
     * @return ProviderContract|null
     * @throws ProviderException|CustomProviderException|\GuzzleHttp\Exception\GuzzleException
     */
    public static function build(string|false|null $name, ?string $ip = null): ?ProviderContract
    {
        if (!self::ipLookupEnabled()) {
            return null;
        }

        return match ($name) {
            'ip2location-lite' => new LocationLite($ip),
            'ip-api' => new IpApi($ip),
            default => self::buildCustomProvider($name, $ip),
        };
    }

    /**
     * Build a custom provider from configuration.
     *
     * @param string|null $name
     * @param string|null $ip
     * @return ProviderContract
     * @throws ProviderException|CustomProviderException
     */
    protected static function buildCustomProvider(?string $name, ?string $ip = null): ProviderContract
    {
        $customProviders = config('tracker.lookup.custom_providers', []);

        if (!isset($customProviders[$name])) {
            throw new ProviderException("Unsupported IP provider: {$name}. Choose 'ip2location-lite', 'ip-api', or define a custom provider in config.");
        }

        $providerClass = $customProviders[$name];

        if (!class_exists($providerClass)) {
            throw new CustomProviderException("Custom provider class not found: {$providerClass}");
        }

        if (!is_a($providerClass, ProviderContract::class, true)) {
            throw new CustomProviderException(
                "Custom IP provider {$providerClass} must implement " . ProviderContract::class
            );
        }

        return new $providerClass($ip);
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
