<?php

namespace Jurager\Tracker\Tests;

use Jurager\Tracker\Traits\Tracking;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class PassportUser extends Authenticatable
{
    use Tracking, HasApiTokens;

    protected $fillable = [
        'id', 'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
