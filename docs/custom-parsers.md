---
title: Custom Parsers
weight: 70
---

# Custom Parsers

Create a custom User-Agent parser by extending `AbstractParser`.

## Example

```php
namespace App\Parsers;

use Jurager\Tracker\Parsers\AbstractParser;

class MyParser extends AbstractParser
{
    protected mixed $parser;

    protected function parse(): void
    {
        $this->parser = new SomeParserLibrary($this->userAgent);
    }

    public function getDevice(): ?string
    {
        return $this->parser->device();
    }

    public function getDeviceType(): ?string
    {
        return $this->parser->deviceType();
    }

    public function getPlatform(): ?string
    {
        return $this->parser->platform();
    }

    public function getBrowser(): ?string
    {
        return $this->parser->browser();
    }
}
```

## Register Parser

```php
'parser' => 'my-parser',
'custom_parsers' => [
    'my-parser' => \App\Parsers\MyParser::class,
],
```

Your parser must implement `Jurager\Tracker\Contracts\ParserContract`.

> [!WARNING]
> Custom parser class must be autoloadable and return `null` for unknown values instead of throwing on malformed User-Agent strings.
