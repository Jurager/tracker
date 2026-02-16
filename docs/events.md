---
title: Events
weight: 80
---

# Events

Tracker dispatches events you can subscribe to.

## TokenCreated

Dispatched when a tracked token is being created.

Payload:
- `personalAccessToken` (`Jurager\Tracker\Models\PersonalAccessToken`)
- `context` (`Jurager\Tracker\Support\RequestContext`)

Example listener registration:

```php
use Jurager\Tracker\Events\TokenCreated;

protected $listen = [
    TokenCreated::class => [
        NotifyUserOfNewLogin::class,
    ],
];
```

## LookupFailed

Dispatched when IP lookup request fails.

Payload:
- `exception`
- `ip`

> [!NOTE]
> Event is informational: token creation continues even when lookup fails.

Example listener registration:

```php
use Jurager\Tracker\Events\LookupFailed;

protected $listen = [
    LookupFailed::class => [
        LogLookupFailure::class,
    ],
];
```
