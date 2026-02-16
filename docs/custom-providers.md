---
title: Custom Providers
weight: 60
---

# Custom Providers

Create a custom IP lookup provider by implementing `ProviderContract` or extending `AbstractProvider`.

## Example

```php
namespace App\IpProviders;

use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Jurager\Tracker\Providers\AbstractProvider;

class MyProvider extends AbstractProvider
{
    public function getRequest(): GuzzleRequest
    {
        return new GuzzleRequest('GET', "https://api.example.com/lookup/{$this->ip}");
    }

    public function getCountry(): ?string
    {
        return $this->result?->get('country');
    }

    public function getRegion(): ?string
    {
        return $this->result?->get('region');
    }

    public function getCity(): ?string
    {
        return $this->result?->get('city');
    }

    public function getCustomData(): array
    {
        return [
            'timezone' => $this->result?->get('timezone'),
            'isp' => $this->result?->get('isp'),
        ];
    }
}
```

## Register Provider

```php
'lookup' => [
    'provider' => 'my-provider',
    'custom_providers' => [
        'my-provider' => \App\IpProviders\MyProvider::class,
    ],
],
```

If your provider has `getCustomData()`, Tracker stores it in `personal_access_tokens.ip_data`.

> [!WARNING]
> Custom provider class must be autoloadable and implement `Jurager\Tracker\Contracts\ProviderContract`.
