<?php

namespace Jurager\Tracker\Tests\Unit;

use Jurager\Tracker\Parsers\Agent;
use Jurager\Tracker\Tests\TestCase;

class ParsersTest extends TestCase
{
    /** @test */
    public function agent_parser_extracts_device_information(): void
    {
        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';

        $parser = new Agent($userAgent);

        $this->assertEquals('desktop', $parser->getDeviceType());
        $this->assertNotNull($parser->getPlatform());
        $this->assertNotNull($parser->getBrowser());
    }

    /** @test */
    public function agent_parser_detects_mobile_devices(): void
    {
        $userAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1';

        $parser = new Agent($userAgent);

        $this->assertEquals('phone', $parser->getDeviceType());
    }

    /** @test */
    public function agent_parser_detects_tablet_devices(): void
    {
        $userAgent = 'Mozilla/5.0 (iPad; CPU OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1';

        $parser = new Agent($userAgent);

        $this->assertEquals('tablet', $parser->getDeviceType());
    }

    /** @test */
    public function agent_parser_returns_null_for_webkit_device(): void
    {
        $parser = new Agent('');

        $this->assertNull($parser->getDevice());
    }

    /** @test */
    public function agent_parser_handles_empty_user_agent(): void
    {
        $parser = new Agent('');

        $this->assertNull($parser->getDevice());
        $this->assertNull($parser->getDeviceType());
        $this->assertNull($parser->getPlatform());
        $this->assertNull($parser->getBrowser());
    }

    /** @test */
    public function agent_parser_extracts_browser_name(): void
    {
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';

        $parser = new Agent($userAgent);

        $this->assertStringContainsString('Chrome', $parser->getBrowser());
    }

    /** @test */
    public function agent_parser_extracts_platform_name(): void
    {
        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36';

        $parser = new Agent($userAgent);

        $this->assertStringContainsString('OS X', $parser->getPlatform());
    }
}
