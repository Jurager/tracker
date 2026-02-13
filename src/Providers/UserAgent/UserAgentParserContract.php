<?php

namespace Jurager\Tracker\Providers\UserAgent;

interface UserAgentParserContract
{
    /**
     * Get the device name.
     *
     * @return string|null
     */
    public function getDevice(): ?string;

    /**
     * Get the device type.
     *
     * @return string|null
     */
    public function getDeviceType(): ?string;

    /**
     * Get the platform name.
     *
     * @return string|null
     */
    public function getPlatform(): ?string;

    /**
     * Get the browser name.
     *
     * @return string|null
     */
    public function getBrowser(): ?string;
}
