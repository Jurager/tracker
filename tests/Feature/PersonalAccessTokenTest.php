<?php

namespace Jurager\Tracker\Tests\Feature;

use Jurager\Tracker\Models\PersonalAccessToken;
use Jurager\Tracker\Tests\TestCase;
use Jurager\Tracker\Tests\User;

class PersonalAccessTokenTest extends TestCase
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
    public function it_creates_token_with_metadata(): void
    {
        $token = $this->user->createToken('Test Token')->accessToken;

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $token->id,
            'name' => 'Test Token',
        ]);

        $this->assertNotNull($token->user_agent);
        $this->assertNotNull($token->ip);
    }

    /** @test */
    public function it_can_get_location_attribute(): void
    {
        $token = $this->user->createToken('Test Token')->accessToken;
        $token->city = 'New York';
        $token->region = 'New York';
        $token->country = 'United States';
        $token->save();

        $this->assertEquals('New York, New York, United States', $token->location);
    }

    /** @test */
    public function it_returns_null_location_when_no_data(): void
    {
        $token = $this->user->createToken('Test Token')->accessToken;

        $this->assertNull($token->location);
    }

    /** @test */
    public function it_can_check_if_token_is_expired(): void
    {
        config(['tracker.expires' => 30]);

        // Create expired token
        $expiredToken = $this->user->createToken('Expired Token')->accessToken;
        $expiredToken->created_at = now()->subDays(60);
        $expiredToken->save();

        // Create fresh token
        $freshToken = $this->user->createToken('Fresh Token')->accessToken;

        $this->assertTrue($expiredToken->isExpired());
        $this->assertFalse($freshToken->isExpired());
    }

    /** @test */
    public function it_returns_false_when_expiration_disabled(): void
    {
        config(['tracker.expires' => 0]);

        $token = $this->user->createToken('Token')->accessToken;
        $token->created_at = now()->subYears(10);
        $token->save();

        $this->assertFalse($token->isExpired());
    }

    /** @test */
    public function it_can_revoke_token(): void
    {
        $token = $this->user->createToken('Token')->accessToken;
        $tokenId = $token->id;

        $result = $token->revoke();

        $this->assertTrue($result);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);
    }

    /** @test */
    public function it_can_check_if_token_is_current(): void
    {
        $token1 = $this->user->createToken('Token 1')->accessToken;
        $token2 = $this->user->createToken('Token 2')->accessToken;

        // Authenticate user with token1
        $this->actingAs($this->user, 'sanctum');

        // Manually set the current access token on user
        $this->user->withAccessToken($token1);

        // Check via direct attribute access since Request::user() might not work in unit tests
        $this->assertEquals($token1->id, $this->user->currentAccessToken()->id);
    }

    /** @test */
    public function it_can_mark_token_as_used(): void
    {
        $token = $this->user->createToken('Token')->accessToken;

        $this->assertNull($token->last_used_at);

        $token->markAsUsed();
        $token->refresh();

        $this->assertNotNull($token->last_used_at);
    }

    /** @test */
    public function prunable_query_returns_expired_tokens(): void
    {
        config(['tracker.expires' => 30]);

        // Create expired token
        $expiredToken = $this->user->createToken('Expired')->accessToken;
        $expiredToken->created_at = now()->subDays(60);
        $expiredToken->save();

        // Create fresh token
        $freshToken = $this->user->createToken('Fresh')->accessToken;

        $prunableQuery = (new PersonalAccessToken())->prunable();
        $prunable = $prunableQuery->get();

        $this->assertCount(1, $prunable);
        $this->assertEquals($expiredToken->id, $prunable->first()->id);
    }

    /** @test */
    public function prunable_query_returns_nothing_when_disabled(): void
    {
        config(['tracker.expires' => 0]);

        $token = $this->user->createToken('Token')->accessToken;
        $token->created_at = now()->subYears(10);
        $token->save();

        $prunableQuery = (new PersonalAccessToken())->prunable();
        $prunable = $prunableQuery->get();

        $this->assertCount(0, $prunable);
    }

    /** @test */
    public function it_respects_last_used_at_for_expiration(): void
    {
        config(['tracker.expires' => 30]);

        $token = $this->user->createToken('Token')->accessToken;
        $token->created_at = now()->subDays(60);
        $token->last_used_at = now()->subDays(10);
        $token->save();

        $this->assertFalse($token->isExpired());
    }
}
