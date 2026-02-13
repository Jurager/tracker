# Laravel Sanctum Tracker

[![Latest Stable Version](https://poser.pugx.org/jurager/tracker/v/stable)](https://packagist.org/packages/jurager/tracker)
[![Total Downloads](https://poser.pugx.org/jurager/tracker/downloads)](https://packagist.org/packages/jurager/tracker)
[![PHP Version Require](http://poser.pugx.org/jurager/tracker/require/php)](https://packagist.org/packages/jurager/tracker)
[![License](https://poser.pugx.org/jurager/tracker/license)](https://packagist.org/packages/jurager/tracker)

Track Laravel Sanctum authentication tokens with detailed metadata including IP addresses, user agents, device information, and optional geolocation data.


- IP Geolocation Tracking (country, region, city)
- Device Detection (type, browser, platform)
- Session Management
- Automatic Token Pruning
- Customizable Providers and Parsers

> [!NOTE]
> The documentation for this package is currently being written. For now, please refer to this readme for information on the functionality and usage of the package.


- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Basic Usage](#basic-usage)
  - [Session Management](#session-management)
  - [Querying Tokens](#querying-tokens)
- [IP Geolocation](#ip-geolocation)
- [Custom Providers](#custom-providers)
- [Events](#events)
- [Testing](#testing)
- [API Reference](#api-reference)
- [License](#license)

## Requirements

- PHP: 8.1 or higher
- Laravel: 10.x, 11.x, or 12.x
- Laravel Sanctum: 3.x or 4.x

## Installation

### Step 1: Install via Composer

```bash
composer require jurager/tracker
```

### Step 2: Publish Configuration

```bash
php artisan vendor:publish --provider="Jurager\Tracker\TrackerServiceProvider" --tag="config"
```

### Step 3: Run Migrations

```bash
php artisan migrate
```

### Step 4: Install a User-Agent Parser

Choose one of the supported parsers:

```bash
# Option 1: jenssegers/agent (Recommended)
composer require jenssegers/agent

# Option 2: whichbrowser/parser
composer require whichbrowser/parser
```

Update `config/tracker.php`:

```php
'parser' => 'agent', // or 'whichbrowser'
```

## Configuration

### Override Sanctum Model

In your `AppServiceProvider` or `AuthServiceProvider`:

```php
use Jurager\Tracker\Models\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

public function boot(): void
{
    Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
}
```

### Add Trackable Trait to User Model

```php
use Jurager\Tracker\Traits\Trackable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Trackable;
}
```

### Setup Auto-Pruning (Optional)

To automatically remove expired tokens, add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('model:prune')->daily();
}
```

Configure expiration in `config/tracker.php`:

```php
'expires' => 90, // Days until tokens expire (0 = never)
```

## Usage

### Basic Usage

The package works transparently with Sanctum. Just create tokens as usual:

```php
$user = User::find(1);
$token = $user->createToken('mobile-app');

// Token metadata is automatically populated
$accessToken = $token->accessToken;

echo $accessToken->device;      // "iPhone"
echo $accessToken->device_type; // "phone"
echo $accessToken->platform;    // "iOS"
echo $accessToken->browser;     // "Safari"
echo $accessToken->ip;          // "192.168.1.1"
echo $accessToken->country;     // "United States" (if geolocation enabled)
echo $accessToken->location;    // "New York, New York, United States"
```

### Session Management

```php
use Illuminate\Http\Request;

class AuthController extends Controller
{
    // Logout from current device
    public function logout(Request $request)
    {
        $request->user()->logout();

        return response()->json(['message' => 'Logged out']);
    }

    // Logout from specific device by token ID
    public function logoutDevice(Request $request, $tokenId)
    {
        $request->user()->logout($tokenId);

        return response()->json(['message' => 'Device logged out']);
    }

    // Logout from all other devices
    public function logoutOthers(Request $request)
    {
        $request->user()->logoutOthers();

        return response()->json(['message' => 'Other devices logged out']);
    }

    // Logout from all devices
    public function logoutAll(Request $request)
    {
        $request->user()->logoutAll();

        return response()->json(['message' => 'All devices logged out']);
    }
}
```

### Querying Tokens

```php
// Get all login history
$allLogins = $user->logins;

// Get recent logins (last 30 days)
$recentLogins = $user->recentLogins(30)->get();

// Get active devices (used in last 30 days)
$activeDevices = $user->activeDevices(30)->get();

// Filter by device type
$mobileLogins = $user->byDevice('mobile')->get();
$desktopLogins = $user->byDevice('desktop')->get();

// Filter by platform
$iosLogins = $user->byPlatform('iOS')->get();
$macLogins = $user->byPlatform('macOS')->get();

// Filter by country
$usLogins = $user->byCountry('United States')->get();
```

### Token Methods

```php
$token = $user->tokens()->first();

// Check if token is expired
if ($token->isExpired()) {
    $token->revoke();
}

// Mark token as used
$token->markAsUsed();

// Check if this is the current token
$currentToken = $request->user()->currentAccessToken();
if ($token->isCurrent($currentToken)) {
    // This is the current token
}
```

## IP Geolocation

### Built-in Provider: IP-API

Enable IP geolocation in `config/tracker.php`:

```php
'lookup' => [
    'provider' => 'ip-api',
    'timeout' => 1.0,
    'retries' => 2,
    'environments' => ['production', 'staging'],
],
```

Now tokens will include geolocation data:

```php
$token = $user->createToken('mobile-app')->accessToken;

echo $token->country; // "United States"
echo $token->region;  // "California"
echo $token->city;    // "San Francisco"
echo $token->location; // "San Francisco, California, United States"
```

### Built-in Provider: IP2Location Lite

1. Download [IP2Location DB3 database](https://lite.ip2location.com/database/ip-country-region-city)
2. Import to your database
3. Configure in `config/tracker.php`:

```php
'lookup' => [
    'provider' => 'ip2location-lite',
    'ip2location' => [
        'ipv4_table' => 'ip2location_db3',
        'ipv6_table' => 'ip2location_db3_ipv6',
    ],
],
```

## Custom Providers

Create a custom IP provider by implementing the `Provider` interface:

```php
namespace App\IpProviders;

use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Jurager\Tracker\Contracts\Provider;
use Jurager\Tracker\Providers\AbstractProvider;

class MyCustomProvider extends AbstractProvider
{
    public function getRequest(): GuzzleRequest
    {
        return new GuzzleRequest(
            'GET',
            "https://api.example.com/lookup/{$this->ip}"
        );
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

    // Optional: Store additional data
    public function getCustomData(): array
    {
        return [
            'timezone' => $this->result?->get('timezone'),
            'isp' => $this->result?->get('isp'),
        ];
    }
}
```

Register in `config/tracker.php`:

```php
'lookup' => [
    'provider' => 'my-provider',
    'custom_providers' => [
        'my-provider' => \App\IpProviders\MyCustomProvider::class,
    ],
],
```

## Events

### TokenCreated Event

Listen to token creation events:

```php
namespace App\Listeners;

use Jurager\Tracker\Events\TokenCreated;

class NotifyUserOfNewLogin
{
    public function handle(TokenCreated $event): void
    {
        $token = $event->personalAccessToken;
        $context = $event->context;

        // Send notification
        $token->tokenable->notify(new NewDeviceLogin(
            device: $token->device,
            location: $token->location,
            ip: $context->ip,
        ));
    }
}
```

Register in `EventServiceProvider`:

```php
use Jurager\Tracker\Events\TokenCreated;

protected $listen = [
    TokenCreated::class => [
        NotifyUserOfNewLogin::class,
    ],
];
```

### LookupFailed Event

Handle IP lookup failures:

```php
use Jurager\Tracker\Events\LookupFailed;

protected $listen = [
    LookupFailed::class => [
        LogIpLookupFailure::class,
    ],
];
```

## Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test:coverage
```

### Writing Tests

```php
use Jurager\Tracker\Models\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

class MyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->accessToken;

        $this->actingAs($user);
        $user->logout();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->id,
        ]);
    }
}
```

## API Reference

### Trackable Trait Methods

| Method | Description |
|--------|-------------|
| `logins()` | Get all login tokens |
| `recentLogins(int $days = 30)` | Get recent logins within specified days |
| `activeDevices(int $days = 30)` | Get active devices (used recently) |
| `byDevice(string $type)` | Filter by device type (desktop, mobile, tablet, phone) |
| `byPlatform(string $platform)` | Filter by platform (iOS, Android, Windows, macOS, etc.) |
| `byCountry(string $country)` | Filter by country name |
| `logout(?int $tokenId = null)` | Logout from current or specific device |
| `logoutOthers()` | Logout from all other devices |
| `logoutAll()` | Logout from all devices |

### PersonalAccessToken Methods

| Method | Description |
|--------|-------------|
| `isExpired()` | Check if token is expired based on config |
| `revoke()` | Delete/revoke the token |
| `isCurrent(?self $token)` | Check if this is the current token |
| `markAsUsed()` | Update last_used_at timestamp |
| `location` | Get formatted location string (accessor) |

## License

Open source, licensed under the [MIT license](LICENSE).
