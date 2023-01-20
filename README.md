# Jurager/Tracker
[![Latest Stable Version](https://poser.pugx.org/jurager/tracker/v/stable)](https://packagist.org/packages/jurager/teams)
[![Total Downloads](https://poser.pugx.org/jurager/tracker/downloads)](https://packagist.org/packages/jurager/teams)
[![PHP Version Require](http://poser.pugx.org/jurager/tracker/require/php)](https://packagist.org/packages/jurager/tracker)
[![License](https://poser.pugx.org/jurager/tracker/license)](https://packagist.org/packages/jurager/teams)

This package allows you to track sanctum tokens by reading the request and recording the IP address, and other metadata to database.

With an IP address lookup, you may retrieve even more information, such as the geolocation, by using a supported provider or establishing your own custom providers.

It also comes with a trait that introduces three useful methods for your user model: 'logout,' 'logoutOthers,' and 'logoutAll.'

* [Requirements](#requirements)
* [Installation](#installation)
  * [Override Sanctum Model](#override-sanctum-model)
  * [Pruning Outdated Records](#pruning-outdated-records)
  * [Install a User-Agent Parser](#install-a-user-agent-parser)
  * [Use the Trait](#use-the-trait)
* [Usage](#usage)
* [IP Lookup](#ip-lookup)
  * [IP2Location Lite](#ip2location-lite)
  * [Custom Provider](#custom-provider)
  * [Handle Errors](#handle-errors)
* [Events](#events)
  * [PersonalAccessTokenCreated](#personalaccesstokencreated)
* [License](#license)

## Requirements

`PHP => 8.0` and `Laravel => 8.x` with `Sanctum => 2.0`

## Installation

```bash
composer require jurager/tracker
```

Publish the configuration with:

```bash
php artisan vendor:publish --provider="Jurager\Tracker\TrackerServiceProvider" --tag="config"
```

Run the migrations to update the tables:

```bash
php artisan migrate
```

### Override Sanctum Model

This package comes with a custom model (`Jurager\Tracker\Models\PersonalAccessToken`) that extends the default Sanctum model.

Instruct Sanctum to use this custom model via the `usePersonalAccessTokenModel` method provided by Sanctum. Typically, you should call this method in the `boot` method of one of your application's service providers:

```php
use Jurager\Tracker\Models\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

/**
 * Bootstrap any application services.
 *
 * @return void
 */
public function boot()
{
    Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
}
```

### Pruning Outdated Records

This package allows you to remove outdated authentication records.

Period for records to expire is described in configuration `tracker.expires`.

Add schedule command to your `Kernel.php`, you can use period as you need

```php
$schedule->command('model:prune')->everyMinute();
```

### Install a User-Agent Parser

This package relies on a User-Agent parser to extract the informations.

Currently supported parsers:
- [Agent](https://github.com/jenssegers/agent)
- [WhichBrowser](https://github.com/WhichBrowser/Parser-PHP)

Before using this package, you need to choose a supported parser.

### Use the Trait

This package provides a `Jurager\Tracker\Traits\Tracked` trait
that can be used on your user model to quickly revoke tokens.

It introduces convenient methods:

- `logout`: to revoke the current token or a specific token by passing its ID in parameter
- `logoutAll`: to revoke all the tokens, including the current one
- `logoutOthers`: to revoke all the tokens, except the current one

```php
use Jurager\Tracker\Traits\Tracked;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Tracked;

    // ...
}
```

## Usage

Use Sanctum as like you would normally do. 

The `PersonalAccessToken` model provided by this package will automatically be populated with the extra informations.

## IP Lookup

By default, package collects the IP and the information given by the User-Agent header.

But you can go even further and collect other data, like the geolocation.

To do so, you first have to enable the IP lookup feature in the configuration file.

This package comes with two officially supported providers for IP address lookup
(see the IP Address Lookup section in the `config/tracker.php` configuration file).

### IP2Location Lite

This package officially support the IP address geolocation with the IP2Location DB3.

Here are the steps to enable and use it:

- Download the database and follow the instructions in the [documentation](https://lite.ip2location.com/database/ip-country-region-city) to import it:

- Set the name of the `lookup.provider` option to `ip2location-lite` in the `config/tracker.php`

- Set the name of the tables used in your database for IPv4 and IPv6 in the `config/tracker.php`

### Custom Provider

You can add your own providers by creating a class that implements the
`Jurager\Tracker\Interfaces\IpProvider` interface and use the `Jurager\Tracker\Traits\MakesApiCalls` trait.

Your custom class have to be registered in the `custom_providers` array of the configuration file.

Let's see an example of an IP lookup provider with the built-in `IpApi` provider:

```php
use Jurager\Tracker\Interfaces\IpProvider;
use Jurager\Tracker\Traits\MakesApiCalls;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Support\Facades\Request;

class IpApi implements IpProvider
{
    use MakesApiCalls;

    /**
     * Get the Guzzle request.
     *
     * @return GuzzleRequest
     */
    public function getRequest()
    {
        return new GuzzleRequest('GET', 'http://ip-api.com/json/'.Request::ip().'?fields=25');
    }

    /**
     * Get the country name.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->result->get('country');
    }

    /**
     * Get the region name.
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->result->get('regionName');
    }

    /**
     * Get the city name.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->result->get('city');
    }
}
```

As you can see, the class has a `getRequest` method that must return a `GuzzleHttp\Psr7\Request` instance.

Guzzle utilizes PSR-7 as the HTTP message interface. Check its documentation:
[http://docs.guzzlephp.org/en/stable/psr7.html](http://docs.guzzlephp.org/en/stable/psr7.html)

The `IpProvider` interface comes with required methods related to the geolocation.
All keys of the API response are accessible in your provider via `$this->result`, which is a Laravel collection.

If you want to collect other informations, you can add a `getCustomData` method in your custom provider.
This custom data will be saved in the logins table in the `ip_data` JSON column.
Let's see an example of additional data:

```php
public function getCustomData()
{
    return [
        'country_code' => $this->result->get('countryCode'),
        'latitude'     => $this->result->get('lat'),
        'longitude'    => $this->result->get('lon'),
        'timezone'     => $this->result->get('timezone'),
        'isp_name'     => $this->result->get('isp'),
    ];
}
```

### Handle Errors

This package fires the `FailedApiCall` event if an exception is triggered during an API request to your IP address lookup provider.

This event has an exception attribute containing the `GuzzleHttp\Exception\TransferException`
(see [Guzzle documentation](http://docs.guzzlephp.org/en/stable/quickstart.html#exceptions)).

This event can be listened to in order to add your own logic.

## Events

### PersonalAccessTokenCreated

You can listen to the `Jurager/Tracker/Events/PersonalAccessTokenCreated` event on a new login.

It has a `personalAccessToken` property, which has the newly created `Jurager/Tracker/Models/PersonalAccessToken` and a `context` property, which receives a `Jurager/Tracker/RequestContext` which contains all the data collected on the request.

Available properties:
```php
$this->context->userAgent; // The full, unparsed, User-Agent header
$this->context->ip;        // The IP address
```

Available methods:
```php
$this->context->parser(); // Returns the parser used to parse the User-Agent header
$this->context->ip();     // Returns the IP address lookup provider
```

Available methods in the parser:
```php
$this->context->parser()->getDevice();     // The name of the device (MacBook...)
$this->context->parser()->getDeviceType(); // The type of the device (desktop, mobile, tablet, phone...)
$this->context->parser()->getPlatform();   // The name of the platform (macOS...)
$this->context->parser()->getBrowser();    // The name of the browser (Chrome...)
```

Available methods in the IP address lookup provider:
```php
$this->context->ip()->getCountry(); // The name of the country
$this->context->ip()->getRegion();  // The name of the region
$this->context->ip()->getCity();    // The name of the city
$this->context->ip()->getResult();  // The entire result of the API call as a Laravel collection

// And all your custom methods in the case of a custom provider
```

## License

Open source, licensed under the [MIT license](LICENSE).
