<?php

namespace Jurager\Tracker\Traits;

trait Tracked
{

     /**
     * Get all logins history
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
	public function logins(): \Illuminate\Database\Eloquent\Relations\MorphMany
	{
		return $this->morphMany(\Jurager\Tracker\Models\PersonalAccessToken::class, 'tokenable');
	}


    /**
     * Revoke an access token by its ID.
     *
     * @param mixed $personalAccessTokenId
     * @return bool
     */
    public function logout($personalAccessTokenId = null)
    {
        $personalAccessToken = $personalAccessTokenId ? $this->tokens()->find($personalAccessTokenId) : $this->currentAccessToken();

        return $personalAccessToken ? !empty($personalAccessToken->delete()) : false;
    }

    /**
     * Revoke all access tokens, except the current one.
     *
     * @return bool
     */
    public function logoutOthers()
    {
        return $this->currentAccessToken() ? !empty($this->tokens()->where('id', '<>', $this->currentAccessToken()->id)->delete()) : false;
    }

    /**
     * Revoke all access tokens.
     *
     * @return bool
     */
    public function logoutAll()
    {
        return !empty($this->tokens()->delete());
    }
}
