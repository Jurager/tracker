<?php

namespace Jurager\Tracker\Factories;

use Jurager\Tracker\Contracts\UserAgentParser;
use Jurager\Tracker\Parsers\Agent;
use Jurager\Tracker\Parsers\WhichBrowser;

class UserAgentParserFactory
{
    /**
     * Build a new user-agent parser.
     *
     * @param string|null $name
     * @return UserAgentParser
     * @throws \Exception
     */
    public static function build(?string $name): UserAgentParser
    {
        return match ($name) {
            'agent' => new Agent(),
            'whichbrowser' => new WhichBrowser(),
            default => throw new \Exception("Unsupported User-Agent parser: {$name}. Choose 'agent' or 'whichbrowser'."),
        };
    }
}
