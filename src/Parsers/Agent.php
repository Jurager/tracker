<?php

namespace Jurager\Tracker\Parsers;

use Jenssegers\Agent\Agent as Parser;

class Agent extends AbstractParser
{
    private ?Parser $parser = null;

    protected function parse(): void
    {
        $userAgent = trim((string) $this->userAgent);

        if ($userAgent === '') {
            return;
        }

        $this->parser = new Parser();
        $this->parser->setUserAgent($userAgent);
    }

    private function parser(): ?Parser
    {
        return $this->parser;
    }

    public function getDevice(): ?string
    {
        $device = $this->parser()?->device();

        return $this->normalize($device, ['WebKit']);
    }

    public function getDeviceType(): ?string
    {
        $parser = $this->parser();

        if (!$parser) {
            return null;
        }

        return match (true) {
            $parser->isDesktop() => 'desktop',
            $parser->isTablet()  => 'tablet',
            $parser->isPhone()   => 'phone',
            $parser->isMobile()  => 'mobile',
            default              => null,
        };
    }

    public function getPlatform(): ?string
    {
        return $this->normalize($this->parser()?->platform());
    }

    public function getBrowser(): ?string
    {
        return $this->normalize($this->parser()?->browser());
    }

    private function normalize(?string $value, array $invalid = []): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || in_array($value, $invalid, true)) {
            return null;
        }

        return $value;
    }
}
