---
title: Introduction
weight: 10
---

# Introduction

Jurager/Tracker extends Laravel Sanctum token records with request metadata and optional geolocation data.

## What It Tracks

- Client IP address
- Raw User-Agent string
- Device type and device name
- Platform and browser
- Optional geolocation: country, region, city
- Optional custom IP payload in `ip_data`

## How It Works

1. Your app uses Sanctum as usual (`createToken`).
2. Tracker model (`Jurager\Tracker\Models\PersonalAccessToken`) is set as Sanctum token model.
3. On token creation, Tracker resolves request context and populates metadata fields.
4. `Trackable` trait provides login/session helper queries and logout helpers.
5. Optional pruning removes expired tokens based on activity.

> [!NOTE]
> Metadata is captured at token creation time. Existing tokens created before enabling Tracker will not be backfilled.

## When To Use

- You need a login/session history per user.
- You need device and platform awareness in security flows.
- You want to revoke specific sessions/devices.
- You need lightweight geolocation for tokens.

## Requirements

- PHP >= 8.1
- Laravel 10, 11, or 12
- Laravel Sanctum 3 or 4
- Optional: `jenssegers/agent` or `whichbrowser/parser`
