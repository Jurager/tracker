<?php

namespace Jurager\Tracker\Factories;

use Jurager\Tracker\Contracts\ParserContract;
use Jurager\Tracker\Exceptions\ParserException;
use Jurager\Tracker\Parsers\Agent;
use Jurager\Tracker\Parsers\WhichBrowser;

class ParserFactory
{
    /**
     * Build a new user-agent parser.
     *
     * @param string|null $name
     * @param string|null $userAgent Optional User-Agent string to parse
     * @return ParserContract
     * @throws ParserException
     */
    public static function build(?string $name, ?string $userAgent = null): ParserContract
    {
        return match ($name) {
            'agent' => new Agent($userAgent),
            'whichbrowser' => new WhichBrowser($userAgent),
            default => self::buildCustomParser($name, $userAgent),
        };
    }

    /**
     * Build a custom parser from configuration.
     *
     * @param string|null $name
     * @param string|null $userAgent
     * @return ParserContract
     * @throws ParserException
     */
    protected static function buildCustomParser(?string $name, ?string $userAgent = null): ParserContract
    {
        $customParsers = config('tracker.custom_parsers', []);

        if (!isset($customParsers[$name])) {
            throw new ParserException("Unsupported User-Agent parser: {$name}. Choose 'agent', 'whichbrowser', or define a custom parser in config.");
        }

        $parserClass = $customParsers[$name];

        if (!class_exists($parserClass)) {
            throw new ParserException("Custom parser class not found: {$parserClass}");
        }

        if (!is_subclass_of($parserClass, ParserContract::class)) {
            throw new ParserException("Custom parser {$parserClass} must implement ParserContract interface.");
        }

        return new $parserClass($userAgent);
    }
}
