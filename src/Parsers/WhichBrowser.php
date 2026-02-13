<?php

namespace Jurager\Tracker\Parsers;

use WhichBrowser\Parser;

class WhichBrowser extends AbstractParser
{
    protected Parser $parser;

    protected function parse(): void
    {
        $this->parser = new Parser($this->userAgent);
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
