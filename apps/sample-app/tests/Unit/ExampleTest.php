<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Example;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_greet_returns_greeting(): void
    {
        $example = new Example();
        $result = $example->greet('World');

        $this->assertSame('Hello, World!', $result);
    }
}
