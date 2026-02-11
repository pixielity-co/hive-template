<?php

declare(strict_types=1);

namespace MonoPhp\SampleApp\Tests\Unit;

use MonoPhp\SampleApp\Example;
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
