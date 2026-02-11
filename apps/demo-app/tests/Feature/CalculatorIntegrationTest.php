<?php

declare(strict_types=1);

namespace MonoPhp\DemoApp\Tests\Feature;

use MonoPhp\Calculator\Calculator;
use PHPUnit\Framework\TestCase;

final class CalculatorIntegrationTest extends TestCase
{
    public function test_calculator_package_is_available(): void
    {
        $calculator = new Calculator();

        $this->assertInstanceOf(Calculator::class, $calculator);
    }

    public function test_calculator_performs_complex_calculation(): void
    {
        $calculator = new Calculator();

        // (10 + 5) * 2 - 8 / 4 = 30 - 2 = 28
        $step1 = $calculator->add(10, 5);        // 15
        $step2 = $calculator->multiply($step1, 2); // 30
        $step3 = $calculator->divide(8, 4);       // 2
        $result = $calculator->subtract($step2, $step3); // 28

        $this->assertSame(28.0, $result);
    }
}
