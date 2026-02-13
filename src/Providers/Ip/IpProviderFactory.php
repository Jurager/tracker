<?php

namespace Jurager\Tracker\Providers\Ip;

use Jurager\Tracker\Exceptions\CustomIpProviderException;
use Jurager\Tracker\Exceptions\IpProviderException;
use Illuminate\Support\Facades\App;

class IpProviderFactory
{
    /**
     * Build a new IP provider.
     *
     * @param string|false|null $name
     * @return IpProviderContract|null
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public static function build(string|false|null $name): ?IpProviderContract
    {
        if (!self::ipLookupEnabled()) {
            return null;
        }

        $customProviders = config('tracker.lookup.custom_providers', []);

        // Check for custom provider
        if (is_array($customProviders) && array_key_exists($name, $customProviders)) {
            $providerClass = $customProviders[$name];

            if (!in_array(IpProviderContract::class, class_implements($providerClass) ?: [])) {
                throw new CustomIpProviderException(
                    "Custom IP provider {$providerClass} must implement " . IpProviderContract::class
                );
            }

            return new $providerClass();
        }

        // Use officially supported provider
        return match ($name) {
            'ip2location-lite' => new Ip2LocationLite(),
            'ip-api' => new IpApi(),
            false, null => null,
            default => throw new IpProviderException("Unsupported IP provider: {$name}"),
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
