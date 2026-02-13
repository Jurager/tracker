<?php

namespace Jurager\Tracker\Tests\Unit;

use Jurager\Tracker\Contracts\ParserContract;
use Jurager\Tracker\Exceptions\CustomProviderException;
use Jurager\Tracker\Exceptions\ProviderException;
use Jurager\Tracker\Factories\ParserFactory;
use Jurager\Tracker\Factories\ProviderFactory;
use Jurager\Tracker\Parsers\Agent;
use Jurager\Tracker\Tests\TestCase;

class FactoriesTest extends TestCase
{
    /** @test */
    public function parser_factory_creates_agent_parser(): void
    {
        $parser = ParserFactory::build('agent');

        $this->assertInstanceOf(Agent::class, $parser);
        $this->assertInstanceOf(ParserContract::class, $parser);
    }

    /** @test */
    public function parser_factory_throws_exception_for_unsupported_parser(): void
    {
        $this->expectException(\Jurager\Tracker\Exceptions\ParserException::class);
        $this->expectExceptionMessage("Unsupported User-Agent parser: invalid");

        ParserFactory::build('invalid');
    }

    /** @test */
    public function provider_factory_returns_null_when_disabled(): void
    {
        config(['tracker.lookup.provider' => false]);

        $provider = ProviderFactory::build(false);

        $this->assertNull($provider);
    }

    /** @test */
    public function provider_factory_returns_null_when_environment_mismatch(): void
    {
        config([
            'tracker.lookup.provider' => 'ip-api',
            'tracker.lookup.environments' => ['production'],
        ]);

        $this->app['env'] = 'testing';

        $provider = ProviderFactory::build('ip-api');

        $this->assertNull($provider);
    }

    /** @test */
    public function provider_factory_throws_exception_for_unsupported_provider(): void
    {
        config([
            'tracker.lookup.provider' => 'invalid',
            'tracker.lookup.environments' => ['testing'],
        ]);

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage("Unsupported IP provider: invalid");

        ProviderFactory::build('invalid');
    }

    /** @test */
    public function provider_factory_validates_custom_provider_interface(): void
    {
        config([
            'tracker.lookup.provider' => 'custom',
            'tracker.lookup.environments' => ['testing'],
            'tracker.lookup.custom_providers' => [
                'custom' => \stdClass::class,
            ],
        ]);

        $this->expectException(CustomProviderException::class);
        $this->expectExceptionMessageMatches('/must implement/');

        ProviderFactory::build('custom');
    }

    /** @test */
    public function ip_lookup_enabled_checks_configuration(): void
    {
        config([
            'tracker.lookup.provider' => 'ip-api',
            'tracker.lookup.environments' => ['testing'],
        ]);

        $this->assertTrue(ProviderFactory::ipLookupEnabled());
    }

    /** @test */
    public function ip_lookup_disabled_when_provider_is_false(): void
    {
        config(['tracker.lookup.provider' => false]);

        $this->assertFalse(ProviderFactory::ipLookupEnabled());
    }

    /** @test */
    public function ip_lookup_disabled_when_provider_is_null(): void
    {
        config(['tracker.lookup.provider' => null]);

        $this->assertFalse(ProviderFactory::ipLookupEnabled());
    }

    /** @test */
    public function ip_lookup_disabled_when_environment_not_allowed(): void
    {
        config([
            'tracker.lookup.provider' => 'ip-api',
            'tracker.lookup.environments' => ['production'],
        ]);

        $this->app['env'] = 'local';

        $this->assertFalse(ProviderFactory::ipLookupEnabled());
    }
}
