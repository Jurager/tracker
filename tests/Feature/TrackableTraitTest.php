<?php

namespace Jurager\Tracker\Tests\Feature;

use Jurager\Tracker\Tests\TestCase;
use Jurager\Tracker\Tests\User;

class TrackableTraitTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    /** @test */
    public function it_can_get_all_logins(): void
    {
        $this->user->createToken('Token 1');
        $this->user->createToken('Token 2');
        $this->user->createToken('Token 3');

        $this->assertCount(3, $this->user->logins);
    }

    /** @test */
    public function it_can_get_recent_logins(): void
    {
        // Create old token
        $oldToken = $this->user->createToken('Old Token')->accessToken;
        $oldToken->created_at = now()->subDays(60);
        $oldToken->save();

        // Create recent tokens
        $this->user->createToken('Recent Token 1');
        $this->user->createToken('Recent Token 2');

        $recentLogins = $this->user->recentLogins(30)->get();

        $this->assertCount(2, $recentLogins);
    }

    /** @test */
    public function it_can_logout_current_token(): void
    {
        $token = $this->user->createToken('Test Token')->accessToken;

        // Set current token on user
        $this->user->withAccessToken($token);

        $result = $this->user->logout();

        $this->assertTrue($result);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->id]);
    }

    /** @test */
    public function it_can_logout_specific_token_by_id(): void
    {
        $token1 = $this->user->createToken('Token 1')->accessToken;
        $token2 = $this->user->createToken('Token 2')->accessToken;

        $result = $this->user->logout($token1->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token1->id]);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $token2->id]);
    }

    /** @test */
    public function it_can_logout_all_tokens(): void
    {
        $this->user->createToken('Token 1');
        $this->user->createToken('Token 2');
        $this->user->createToken('Token 3');

        $result = $this->user->logoutAll();

        $this->assertTrue($result);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    /** @test */
    public function it_can_logout_others(): void
    {
        $token1 = $this->user->createToken('Token 1')->accessToken;
        $token2 = $this->user->createToken('Token 2')->accessToken;
        $token3 = $this->user->createToken('Token 3')->accessToken;

        // Set token2 as current
        $this->user->withAccessToken($token2);

        $result = $this->user->logoutOthers();

        $this->assertTrue($result);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token1->id]);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $token2->id]);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token3->id]);
    }

    /** @test */
    public function it_returns_false_when_logout_others_without_current_token(): void
    {
        $this->user->createToken('Token 1');
        $this->user->createToken('Token 2');

        $result = $this->user->logoutOthers();

        $this->assertFalse($result);
        $this->assertDatabaseCount('personal_access_tokens', 2);
    }

    /** @test */
    public function it_can_filter_logins_by_device(): void
    {
        $token1 = $this->user->createToken('Token 1')->accessToken;
        $token1->device_type = 'desktop';
        $token1->save();

        $token2 = $this->user->createToken('Token 2')->accessToken;
        $token2->device_type = 'mobile';
        $token2->save();

        $desktopLogins = $this->user->byDevice('desktop')->get();
        $mobileLogins = $this->user->byDevice('mobile')->get();

        $this->assertCount(1, $desktopLogins);
        $this->assertCount(1, $mobileLogins);
    }

    /** @test */
    public function it_can_filter_logins_by_platform(): void
    {
        $token1 = $this->user->createToken('Token 1')->accessToken;
        $token1->platform = 'macOS';
        $token1->save();

        $token2 = $this->user->createToken('Token 2')->accessToken;
        $token2->platform = 'Windows';
        $token2->save();

        $macLogins = $this->user->byPlatform('macOS')->get();

        $this->assertCount(1, $macLogins);
        $this->assertEquals('macOS', $macLogins->first()->platform);
    }

    /** @test */
    public function it_can_filter_logins_by_country(): void
    {
        $token1 = $this->user->createToken('Token 1')->accessToken;
        $token1->country = 'United States';
        $token1->save();

        $token2 = $this->user->createToken('Token 2')->accessToken;
        $token2->country = 'Canada';
        $token2->save();

        $usLogins = $this->user->byCountry('United States')->get();

        $this->assertCount(1, $usLogins);
        $this->assertEquals('United States', $usLogins->first()->country);
    }
}
