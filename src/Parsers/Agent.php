<?php

namespace Jurager\Tracker\Parsers;

use Illuminate\Support\Facades\Request;
use Jenssegers\Agent\Agent as Parser;
use Jurager\Tracker\Contracts\UserAgentParser;

class Agent implements UserAgentParser
{
    protected Parser $parser;

    /**
     * Agent constructor.
     *
     * @param string|null $userAgent Optional User-Agent string. If not provided, will use current request's User-Agent.
     */
    public function __construct(?string $userAgent = null)
    {
        $this->parser = new Parser();
        $this->parser->setUserAgent($userAgent ?? Request::userAgent() ?? '');
    }

    /**
     * Get the device name.
     *
     * @return string|null
     */
    public function getDevice(): ?string
    {
        $device = $this->parser->device();

        return $device && $device !== 'WebKit' ? $device : null;
    }

    /**
     * Get the device type.
     *
     * @return string|null
     */
    public function getDeviceType(): ?string
    {
        if ($this->parser->isDesktop()) {
            return 'desktop';
        }

        if ($this->parser->isMobile()) {
            return match (true) {
                $this->parser->isTablet() => 'tablet',
                $this->parser->isPhone() => 'phone',
                default => 'mobile',
            };
        }

        return null;
    }

    /**
     * Get the platform name.
     *
     * @return string|null
     */
    public function getPlatform(): ?string
    {
        return $this->parser->platform() ?: null;
    }

    /**
     * Get the browser name.
     *
     * @return string|null
     */
    public function getBrowser(): ?string
    {
        return $this->parser->browser() ?: null;
    }
}
