---
title: Testing
weight: 100
---

# Testing

Run package tests:

```bash
composer test
```

Run tests with coverage report:

```bash
composer test:coverage
```

## Integration Test Setup

When writing app-level tests, make sure Sanctum uses Tracker's token model:

```php
use Jurager\Tracker\Models\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

protected function setUp(): void
{
    parent::setUp();

    Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
}
```

> [!NOTE]
> Without model override in tests, assertions for Tracker metadata fields may fail because default Sanctum model is used.
