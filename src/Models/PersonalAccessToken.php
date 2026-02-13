<?php

namespace Jurager\Tracker\Models;

use Jurager\Tracker\Events\TokenCreated;
use Jurager\Tracker\Support\RequestContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
use Illuminate\Database\Eloquent\MassPrunable;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    use MassPrunable;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'abilities'    => 'json',
        'last_used_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'token',
        'abilities',
        'browser',
        'city',
        'country',
        'device',
        'device_type',
        'ip',
        'ip_data',
        'platform',
        'region',
        'user_agent',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_current',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function (self $personalAccessToken): void {
            // Get as much information as possible about the request
            $context = RequestContext::current();

            $parser = $context->parser();

            $personalAccessToken->forceFill([
                'user_agent'  => $context->userAgent,
                'ip'          => $context->ip,
                'device_type' => $parser->getDeviceType(),
                'device'      => $parser->getDevice(),
                'platform'    => $parser->getPlatform(),
                'browser'     => $parser->getBrowser(),
            ]);

            // If we have the IP geolocation data
            $ipProvider = $context->ip();

            if ($ipProvider) {
                $personalAccessToken->forceFill([
                    'city'    => $ipProvider->getCity(),
                    'region'  => $ipProvider->getRegion(),
                    'country' => $ipProvider->getCountry(),
                ]);

                // Custom additional data?
                if (method_exists($ipProvider, 'getCustomData')) {
                    $customData = $ipProvider->getCustomData();

                    if ($customData) {
                        $personalAccessToken->ip_data = $customData;
                    }
                }
            }

            // Dispatch event
            event(new TokenCreated($personalAccessToken, $context));
        });
    }

    /**
     * Add the "location" attribute to get the IP address geolocation.
     *
     * @return string|null
     */
    public function getLocationAttribute(): ?string
    {
        $location = array_filter([
            $this->city,
            $this->region,
            $this->country,
        ]);

        return $location ? implode(', ', $location) : null;
    }

    /**
     * Dynamicly add the "is_current" attribute.
     *
     * @return bool
     */
    public function getIsCurrentAttribute(): bool
    {
        $user = Request::user();

        if (!$user) {
            return false;
        }

        $currentToken = $user->currentAccessToken();

        return $currentToken && $this->id === $currentToken->id;
    }

    /**
     * Get the prunable model query.
     *
     * @return Builder
     */
    public function prunable(): Builder
    {
        $expires = (int) config('tracker.expires', 0);

        // If pruning is disabled, return a query that matches nothing
        if ($expires <= 0) {
            return $this->whereNull('id');
        }

        $expiryDate = now()->subDays($expires);

        // Return tokens that:
        // 1. Have been used, but not recently (last_used_at is old)
        // 2. Have never been used, but were created long ago (created_at is old)
        return $this->where(function (Builder $query) use ($expiryDate) {
            $query->where('last_used_at', '<=', $expiryDate)
                ->orWhere(function (Builder $query) use ($expiryDate) {
                    $query->whereNull('last_used_at')
                        ->where('created_at', '<=', $expiryDate);
                });
        });
    }

    /**
     * Check if the token is expired based on config.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        $expires = (int) config('tracker.expires', 0);

        if ($expires <= 0) {
            return false;
        }

        $expiryDate = now()->subDays($expires);
        $lastActivity = $this->last_used_at ?? $this->created_at;

        return $lastActivity && $lastActivity->lte($expiryDate);
    }

    /**
     * Revoke (delete) the token.
     *
     * @return bool
     */
    public function revoke(): bool
    {
        return (bool) $this->delete();
    }

    /**
     * Check if this token is the current one being used.
     *
     * @return bool
     */
    public function isCurrent(): bool
    {
        return $this->getIsCurrentAttribute();
    }

    /**
     * Mark the token as used.
     *
     * @return bool
     */
    public function markAsUsed(): bool
    {
        return $this->forceFill(['last_used_at' => now()])->save();
    }
}
