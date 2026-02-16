---
title: API Reference
weight: 90
---

# API Reference

## Trackable Trait

`Jurager\Tracker\Traits\Trackable`

- `logins(): MorphMany`
- `recentLogins(int $days = 30): MorphMany`
- `activeDevices(int $days = 30): MorphMany`
- `byDevice(string $deviceType): MorphMany`
- `byPlatform(string $platform): MorphMany`
- `byCountry(string $country): MorphMany`
- `logout(int|string|null $tokenId = null): bool`
- `logoutOthers(): bool`
- `logoutAll(): bool`

## PersonalAccessToken Model

`Jurager\Tracker\Models\PersonalAccessToken`

- Accessors: `location`, `is_current`
- `isExpired(): bool`
- `revoke(): bool`
- `isCurrent(): bool`
- `markAsUsed(): bool`
- `prunable(): Builder`

## Factories

- `ParserFactory::build(?string $name, ?string $userAgent = null): ParserContract`
- `ProviderFactory::build(string|false|null $name, ?string $ip = null): ?ProviderContract`
- `ProviderFactory::ipLookupEnabled(): bool`

## Contracts

- `ParserContract`
  - `getDevice(): ?string`
  - `getDeviceType(): ?string`
  - `getPlatform(): ?string`
  - `getBrowser(): ?string`
- `ProviderContract`
  - `getRequest(): Request`
  - `getCountry(): ?string`
  - `getRegion(): ?string`
  - `getCity(): ?string`
  - `getResult(): ?Collection`
