<?php

namespace Jurager\Tracker\Providers\UserAgent;

use Illuminate\Support\Facades\Request;
use WhichBrowser\Parser;

use Jurager\Tracker\Providers\UserAgentParserContract;

class WhichBrowser implements UserAgentParserContract
{
    protected Parser $parser;

    /**
     * WhichBrowser constructor.
     */
    public function __construct()
    {
        $userAgent = Request::userAgent() ?? '';
        $this->parser = new Parser($userAgent);
    }

    /**
     * Get the device name.
     *
     * @return string|null
     */
    public function getDevice(): ?string
    {
        $device = trim($this->parser->device->toString());

        return $device ?: $this->getDeviceByManufacturerAndModel();
    }

    /**
     * Get the device name by manufacturer and model.
     *
     * @return string|null
     */
    protected function getDeviceByManufacturerAndModel(): ?string
    {
        $manufacturer = $this->parser->device->getManufacturer() ?? '';
        $model = $this->parser->device->getModel() ?? '';

        $device = trim("{$manufacturer} {$model}");

        return $device ?: null;
    }

    /**
     * Get the device type.
     *
     * @return string|null
     */
    public function getDeviceType(): ?string
    {
        $type = $this->parser->device->type ?? '';

        return trim($type) ?: null;
    }

    /**
     * Get the platform name.
     *
     * @return string|null
     */
    public function getPlatform(): ?string
    {
        return trim($this->parser->os->toString()) ?: null;
    }

    /**
     * Get the browser name.
     *
     * @return string|null
     */
    public function getBrowser(): ?string
    {
        return $this->parser->browser->name ?? null;
    }
}
