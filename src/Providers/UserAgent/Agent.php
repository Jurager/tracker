<?php

namespace Jurager\Tracker\Providers\UserAgent;

use Jenssegers\Agent\Agent as Parser;

class Agent implements UserAgentParserContract
{
    protected Parser $parser;

    /**
     * Agent constructor.
     */
    public function __construct()
    {
        $this->parser = new Parser();
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
