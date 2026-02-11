<?php

declare(strict_types=1);

namespace MonoPhp\SampleApp;

class Example
{
    public function greet(string $name): string
    {
        return "Hello, {$name}!";
    }
}
