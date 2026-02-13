<?php

namespace Jurager\Tracker\Parsers;

use Illuminate\Support\Facades\Request;

/**
 * Abstract base class for User-Agent parsers.
 *
 * Extend this class to create your own parser with automatic User-Agent handling.
 * You only need to implement the parsing methods.
 *
 * Example:
 * ```php
 * class MyCustomParser extends AbstractParser
 * {
 *     protected function parse(): void
 *     {
 *         // Parse the $this->userAgent string
 *         // Store results in properties or use a parser library
 *     }
 *
 *     public function getDevice(): ?string
 *     {
 *         return $this->extractedDevice;
 *     }
 *
 *     // ... implement other methods
 * }
 * ```
 */
abstract class AbstractParser implements UserAgentParser
{
    protected string $userAgent;

    /**
     * Initialize the parser with the User-Agent string.
     *
     * @param string|null $userAgent Optional User-Agent string. If not provided, will use current request's User-Agent.
     */
    public function __construct(?string $userAgent = null)
    {
        $this->userAgent = $userAgent ?? Request::userAgent() ?? '';
        $this->parse();
    }

    /**
     * Parse the User-Agent string.
     * Override this method to implement your parsing logic.
     *
     * @return void
     */
    protected function parse(): void
    {
        // Default implementation does nothing
        // Override in your custom parser
    }

    /**
     * Get the device name from the User-Agent.
     *
     * @return string|null
     */
    abstract public function getDevice(): ?string;

    /**
     * Get the device type from the User-Agent.
     *
     * @return string|null
     */
    abstract public function getDeviceType(): ?string;

    /**
     * Get the platform name from the User-Agent.
     *
     * @return string|null
     */
    abstract public function getPlatform(): ?string;

    /**
     * Get the browser name from the User-Agent.
     *
     * @return string|null
     */
    abstract public function getBrowser(): ?string;
}
