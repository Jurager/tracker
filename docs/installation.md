---
title: Installation
weight: 20
---

# Installation

Install package, publish config, migrate, and select a parser.

## Install

```bash
composer require jurager/tracker
```

## Publish Configuration

```bash
php artisan vendor:publish --provider="Jurager\Tracker\TrackerServiceProvider" --tag="config"
```

## Run Migrations

```bash
php artisan migrate
```

Tracker updates `personal_access_tokens` with metadata columns.

> [!WARNING]
> Sanctum must be installed and its `personal_access_tokens` table must exist before running Tracker migration.

## Install a Parser

Pick one parser implementation:

```bash
# Recommended
composer require jenssegers/agent

# Alternative
composer require whichbrowser/parser
```

Then set parser in `config/tracker.php`:

```php
'parser' => 'agent', // or 'whichbrowser'
```

> [!WARNING]
> If parser package is not installed for the selected driver, parser resolution will fail and token metadata may be incomplete.

## Use Tracker Token Model

Configure Sanctum in a service provider:

```php
use Jurager\Tracker\Models\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

public function boot(): void
{
    Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
}
```

> [!WARNING]
> Without Sanctum model override, Tracker hooks will not run and token fields like `device`, `platform`, and `ip` will stay empty.

## Add Trackable Trait

Add `Trackable` to your user model:

```php
use Jurager\Tracker\Traits\Trackable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Trackable;
}
```
