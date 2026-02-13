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
     * Revoke an access token by its ID.
     *
     * @param int|string|null $personalAccessTokenId
     * @return bool
     */
    public function logout(int|string|null $personalAccessTokenId = null): bool
    {
        $personalAccessToken = $personalAccessTokenId
            ? $this->tokens()->find($personalAccessTokenId)
            : $this->currentAccessToken();

        return $personalAccessToken ? (bool) $personalAccessToken->delete() : false;
    }

    /**
     * Revoke all access tokens, except the current one.
     *
     * @return bool
     */
    public function logoutOthers(): bool
    {
        $currentToken = $this->currentAccessToken();

        if (!$currentToken) {
            return false;
        }

        return (bool) $this->tokens()->where('id', '<>', $currentToken->id)->delete();
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
