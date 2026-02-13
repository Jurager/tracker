<?php

namespace Jurager\Tracker\Factories;

use Jurager\Tracker\Contracts\ParserContract;
use Jurager\Tracker\Parsers\AgentContract;
use Jurager\Tracker\Parsers\WhichBrowser;

class UserAgentParserFactory
{
    /**
     * Build a new user-agent parser.
     *
     * @param string|null $name
     * @param string|null $userAgent Optional User-Agent string to parse
     * @return ParserContract
     * @throws \Exception
     */
    public static function build(?string $name, ?string $userAgent = null): ParserContract
    {
        return match ($name) {
            'agent' => new AgentContract($userAgent),
            'whichbrowser' => new WhichBrowser($userAgent),
            default => throw new \Exception("Unsupported User-Agent parser: {$name}. Choose 'agent' or 'whichbrowser'."),
        };
    }
}
