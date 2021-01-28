<?php

namespace Jurager\Tracker\Traits;

use Jurager\Tracker\EloquentQueryBuilder;
use Jurager\Tracker\Scopes\ExpirationScope;
use Carbon\Carbon;
use Illuminate\Support\Collection as BaseCollection;

trait Expirable
{
	/**
	 * The "booting" method of the model.
	 *
	 * @return void
	 */
	protected static function boot()
	{
		parent::boot();

		static::addGlobalScope(new ExpirationScope);

		static::creating(function($model) {
			// Set the default expiration date if needed
			if (! array_key_exists('expires_at', $model->attributes)) {
				$model->attributes['expires_at'] = null;
			}
		});
	}

	/**
	 * Set the expiration date and return the instance.
	 *
	 * @param object|null $expirationDate
	 * @return self
	 */
	public function expiresAt($expirationDate)
	{
		$this->expires_at = $expirationDate;

		return $this;
	}

	/**
	 * Set the lifetime in a more human readable way and return the instance.
	 *
	 * @param string|null $period
	 * @return self
	 */
	public function lifetime($period)
	{
		$this->expires_at = is_string($period) ? Carbon::now()->add($period) : null;

		return $this;
	}

	/**
	 * Revive an expired model.
	 *
	 * @param object|string|null $newExpirationDate
	 * @return bool
	 */
	public function revive($newExpirationDate = null)
	{
		if ($this->isExpired()) {

			if (is_string($newExpirationDate)) {
				$newExpirationDate = Carbon::now()->add($newExpirationDate);
			} elseif (is_null($newExpirationDate)) {
				$newExpirationDate = null;
			}

			$this->expires_at = $newExpirationDate;

			return $this->save();
		}

		return false;
	}

	/**
	 * Make a model eternal (set expiration date to null).
	 *
	 * @return bool
	 */
	public function makeEternal()
	{
		$this->expires_at = null;

		return $this->save();
	}

	/**
	 * Set the status to "expired" at the current timestamp.
	 *
	 * @return bool
	 */
	public function expire()
	{
		$this->expires_at = Carbon::now();

		return $this->save();
	}

	/**
	 * Set the status to "expired" for the given model IDs.
	 *
	 * @param  \Illuminate\Support\Collection|array|int  $ids
	 * @return int
	 */
	public static function expireByKey($ids)
	{
		// Support for collections
		if ($ids instanceof BaseCollection) {
			$ids = $ids->all();
		}

		// Convert parameters into an array if needed
		$ids = is_array($ids) ? $ids : func_get_args();

		// Create a new static instance and get the primary key for the model
		$key = ($instance = new static)->getKeyName();

		// Perform the query
		return $instance->whereIn($key, $ids)->expire();
	}

	/**
	 * Check for expired model.
	 *
	 * @return bool
	 */
	public function isExpired()
	{
		return !is_null($this->expires_at) && $this->expires_at <= Carbon::now();
	}

	/**
	 * Check if the model is eternal.
	 *
	 * @return bool
	 */
	public function isEternal()
	{
		return is_null($this->expires_at);
	}

	/**
	 * Create a new Eloquent query builder for the model.
	 *
	 * @param  \Illuminate\Database\Query\Builder  $query
	 * @return \Illuminate\Database\Eloquent\Builder|static
	 */
	public function newEloquentBuilder($query)
	{
		return new EloquentQueryBuilder($query);
	}
}