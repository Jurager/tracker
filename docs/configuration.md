---
title: Configuration
weight: 30
---

# Configuration

All options are in `config/tracker.php`.

## Parser

```php
'parser' => 'agent',
'custom_parsers' => [],
```

Supported built-ins:
- `agent`
- `whichbrowser`

Or define your own parser key in `custom_parsers`.

> [!NOTE]
> `parser` value must match an installed built-in driver or a key from `custom_parsers`.

## Token Expiration / Pruning

```php
'expires' => 14,
```

- Value is in days.
- `0` disables expiration-based pruning.
- Expiration checks use `last_used_at` or fallback to `created_at`.

> [!WARNING]
> Pruning does nothing until you schedule and run `model:prune` in your application.

To prune automatically, schedule Laravel pruning:

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('model:prune')->daily();
}
```

## IP Lookup

```php
'lookup' => [
    'provider' => false,
    'timeout' => 1.0,
    'retries' => 2,
    'environments' => ['production'],
    'custom_providers' => [],
    'ip2location' => [
        'ipv4_table' => 'ip2location_db3',
        'ipv6_table' => 'ip2location_db3_ipv6',
    ],
],
```

- `provider`: `false`, `ip-api`, `ip2location-lite`, or custom key.
- `timeout`: HTTP connect/read timeout in seconds.
- `retries`: retry attempts with exponential backoff.
- `environments`: lookup runs only in these app environments.

> [!NOTE]
> Default `environments` is `['production']`, so geolocation is skipped in `local` unless you explicitly add it.
