<?php

namespace Jurager\Tracker\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Jurager\Tracker\Models\PersonalAccessToken;

trait Trackable
{
    /**
     * Get all logins history
     *
     * @return MorphMany
     */
    public function logins(): MorphMany
    {
        return $this->morphMany(PersonalAccessToken::class, 'tokenable');
    }

    /**
     * Get recent logins within the specified number of days.
     *
     * @param int $days
     * @return MorphMany
     */
    public function recentLogins(int $days = 30): MorphMany
    {
        return $this->logins()->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get active devices (tokens used within the specified number of days).
     *
     * @param int $days
     * @return MorphMany
     */
    public function activeDevices(int $days = 30): MorphMany
    {
        return $this->logins()->where(function ($query) use ($days) {
            $query->where('last_used_at', '>=', now()->subDays($days))
                ->orWhere(function ($query) use ($days) {
                    $query->whereNull('last_used_at')
                        ->where('created_at', '>=', now()->subDays($days));
                });
        });
    }

    /**
     * Get logins by device type.
     *
     * @param string $deviceType
     * @return MorphMany
     */
    public function byDevice(string $deviceType): MorphMany
    {
        return $this->logins()->where('device_type', $deviceType);
    }

    /**
     * Get logins by platform.
     *
     * @param string $platform
     * @return MorphMany
     */
    public function byPlatform(string $platform): MorphMany
    {
        return $this->logins()->where('platform', $platform);
    }

    /**
     * Get logins by country.
     *
     * @param string $country
     * @return MorphMany
     */
    public function byCountry(string $country): MorphMany
    {
        return $this->logins()->where('country', $country);
    }

    /**
     * Revoke an access token by its ID.
     *
     * @param int|string|null $personalAccessTokenId
     * @return bool
     */
    public function logout(int|string|null $personalAccessTokenId = null): bool
    {
        $token = $personalAccessTokenId
            ? $this->tokens()->find($personalAccessTokenId)
            : $this->currentAccessToken();

        return (bool) $token?->delete();
    }

    /**
     * Revoke all access tokens, except the current one.
     *
     * @return bool
     */
    public function logoutOthers(): bool
    {
        $currentToken = $this->currentAccessToken();

        return $currentToken
            ? (bool) $this->tokens()->where('id', '<>', $currentToken->id)->delete()
            : false;
    }

    /**
     * Revoke all access tokens.
     *
     * @return bool
     */
    public function logoutAll(): bool
    {
        return (bool) $this->tokens()->delete();
    }
}
