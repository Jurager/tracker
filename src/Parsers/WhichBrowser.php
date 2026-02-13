<?php

namespace Jurager\Tracker\Parsers;

use Illuminate\Support\Facades\Request;
use WhichBrowser\Parser;
use Jurager\Tracker\Contracts\ParserContract;

class WhichBrowser implements ParserContract
{
    protected Parser $parser;

    /**
     * WhichBrowser constructor.
     *
     * @param string|null $userAgent Optional User-Agent string. If not provided, will use current request's User-Agent.
     */
    public function __construct(?string $userAgent = null)
    {
        $userAgent = $userAgent ?? Request::userAgent() ?? '';
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

        return $device !== '' ? $device : $this->getDeviceByManufacturerAndModel();
    }

    /**
     * Get the device name by manufacturer and model.
     *
     * @return string|null
     */
    protected function getDeviceByManufacturerAndModel(): ?string
    {
        $device = trim(sprintf(
            '%s %s',
            $this->parser->device->getManufacturer() ?? '',
            $this->parser->device->getModel() ?? ''
        ));

        return $device !== '' ? $device : null;
    }

    /**
     * Get the device type.
     *
     * @return string|null
     */
    public function getDeviceType(): ?string
    {
        $type = trim($this->parser->device->type ?? '');

        return $type !== '' ? $type : null;
    }

    /**
     * Get the platform name.
     *
     * @return string|null
     */
    public function getPlatform(): ?string
    {
        $platform = trim($this->parser->os->toString());

        return $platform !== '' ? $platform : null;
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
