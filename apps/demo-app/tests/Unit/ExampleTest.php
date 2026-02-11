<?php

declare(strict_types=1);

namespace MonoPhp\DemoApp\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase
{
    public function test_example_assertion(): void
    {
        $this->assertTrue(true);
    }

    public function test_string_concatenation(): void
    {
        $result = 'Hello' . ' ' . 'World';

        $this->assertSame('Hello World', $result);
    }

    public function test_array_operations(): void
    {
        $array = [1, 2, 3];

        $this->assertCount(3, $array);
        $this->assertContains(2, $array);
    }
}
