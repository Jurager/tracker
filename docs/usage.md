---
title: Usage
weight: 40
---

# Usage

Tracker works transparently with Sanctum token creation.

## Create Tokens

```php
$user = User::find(1);
$tokenResult = $user->createToken('mobile-app');
$token = $tokenResult->accessToken;

$token->device;      // e.g. iPhone
$token->device_type; // e.g. phone
$token->platform;    // e.g. iOS
$token->browser;     // e.g. Safari
$token->ip;          // e.g. 203.0.113.10
$token->location;    // e.g. New York, New York, United States
```

> [!NOTE]
> `location` is assembled from `city`, `region`, and `country`; it can be `null` when IP lookup is disabled or unavailable.

## Query Login History

```php
$all = $user->logins;
$recent = $user->recentLogins(30)->get();
$active = $user->activeDevices(30)->get();

$mobile = $user->byDevice('mobile')->get();
$ios = $user->byPlatform('iOS')->get();
$us = $user->byCountry('United States')->get();
```

## Session Management

```php
// Current token
$user->logout();

// Specific token
$user->logout($tokenId);

// All except current
$user->logoutOthers();

// All tokens
$user->logoutAll();
```

> [!WARNING]
> `logoutOthers()` returns `false` when there is no current access token in request context.

## Token Helpers

```php
if ($token->isExpired()) {
    $token->revoke();
}

$token->markAsUsed();
$token->isCurrent();
```
