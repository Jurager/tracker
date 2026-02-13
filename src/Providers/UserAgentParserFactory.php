<?php

namespace Jurager\Tracker\Providers;

use Jurager\Tracker\Providers\UserAgent\Agent;
use Jurager\Tracker\Providers\UserAgent\WhichBrowser;

class UserAgentParserFactory
{
    /**
     * Build a new user-agent parser.
     *
     * @param string|null $name
     * @return UserAgentParserContract
     * @throws \Exception
     */
    public static function build(?string $name): UserAgentParserContract
    {
        return match ($name) {
            'agent' => new Agent(),
            'whichbrowser' => new WhichBrowser(),
            default => throw new \Exception("Unsupported User-Agent parser: {$name}. Choose 'agent' or 'whichbrowser'."),
        };
    }
}
