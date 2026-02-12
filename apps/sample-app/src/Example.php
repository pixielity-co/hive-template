<?php

declare(strict_types=1);

namespace App;

class Example
{
    public function greet(string $name): string
    {
        return "Hello, {$name}!";
    }
}
