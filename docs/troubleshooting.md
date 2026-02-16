---
title: Troubleshooting
weight: 110
---

# Troubleshooting

## Metadata Fields Are Empty

Checklist:

- Sanctum model override points to `Jurager\Tracker\Models\PersonalAccessToken`.
- `Trackable` trait is added to your user model.
- Migration ran successfully and columns exist on `personal_access_tokens`.
- Parser dependency (`jenssegers/agent` or `whichbrowser/parser`) is installed.

> [!WARNING]
> If tokens were created before Tracker was configured, those rows will remain without Tracker metadata.

## Geolocation Is Missing

Checklist:

- `lookup.provider` is enabled (not `false`).
- Current app environment is listed in `lookup.environments`.
- Provider configuration is valid (`ip-api` or `ip2location-lite`).
- For IP2Location, configured table names match imported DB tables.

> [!NOTE]
> Local requests often come from `127.0.0.1`; lookup is skipped for loopback/invalid IP addresses.

## Token Pruning Does Not Work

Checklist:

- `tracker.expires` is greater than `0`.
- You scheduled `model:prune` in your app scheduler.
- Scheduler is running on server (`schedule:run` cron).

## Unexpected Provider/Parser Exceptions

- Verify custom classes exist and are autoloadable.
- Custom parser must implement `ParserContract`.
- Custom provider must implement `ProviderContract`.
- Check application logs for reported exceptions from `RequestContext`.
