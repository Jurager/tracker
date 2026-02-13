<?php

namespace Jurager\Tracker\Parsers;

use Illuminate\Support\Facades\Request;
use Jenssegers\Agent\Agent as Parser;
use Jurager\Tracker\Contracts\ParserContract;

class Agent implements ParserContract
{
    protected Parser $parser;
    protected string $userAgent;
    protected bool $isEmpty;

    public function __construct(?string $userAgent = null)
    {
        $this->userAgent = trim($userAgent ?? Request::userAgent() ?? '');
        $this->isEmpty = $this->userAgent === '';

        $this->parser = new Parser();

        if (!$this->isEmpty) {
            $this->parser->setUserAgent($this->userAgent);
        }
    }

    private function hasUserAgent(): bool
    {
        return !$this->isEmpty;
    }

    public function getDevice(): ?string
    {
        if (!$this->hasUserAgent()) {
            return null;
        }

        $device = $this->parser->device();

        return ($device && $device !== 'WebKit' && trim((string) $device) !== '')
            ? $device
            : null;
    }

    public function getDeviceType(): ?string
    {
        if (!$this->hasUserAgent()) {
            return null;
        }

        return match (true) {
            $this->parser->isDesktop() => 'desktop',
            $this->parser->isTablet()  => 'tablet',
            $this->parser->isPhone()   => 'phone',
            $this->parser->isMobile()  => 'mobile',
            default => null,
        };
    }

    public function getPlatform(): ?string
    {
        return $this->hasUserAgent()
            ? ($this->parser->platform() ?: null)
            : null;
    }

    public function getBrowser(): ?string
    {
        return $this->hasUserAgent()
            ? ($this->parser->browser() ?: null)
            : null;
    }
}