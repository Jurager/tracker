---
title: Geolocation
weight: 50
---

# Geolocation

Tracker can enrich token records with country, region, and city.

## IP-API Provider

Set provider to `ip-api`:

```php
'lookup' => [
    'provider' => 'ip-api',
    'timeout' => 1.0,
    'retries' => 2,
    'environments' => ['production', 'staging'],
],
```

> [!WARNING]
> Built-in `ip-api` endpoint uses HTTP. Review your security/privacy requirements before enabling it in production.

## IP2Location Lite Provider

1. Download IP2Location DB3 dataset.
2. Import IPv4/IPv6 tables into your database.
3. Configure provider and table names:

```php
'lookup' => [
    'provider' => 'ip2location-lite',
    'ip2location' => [
        'ipv4_table' => 'ip2location_db3',
        'ipv6_table' => 'ip2location_db3_ipv6',
    ],
],
```

> [!WARNING]
> Table names must match imported IP2Location tables exactly, otherwise lookups return empty results.

## Notes

- Lookup runs only for configured `lookup.environments`.
- Localhost or invalid IP values are ignored.
- On provider failures, package dispatches `LookupFailed`.

> [!NOTE]
> For most local/dev flows, request IP is often loopback (`127.0.0.1`), so no geolocation data will be resolved.
