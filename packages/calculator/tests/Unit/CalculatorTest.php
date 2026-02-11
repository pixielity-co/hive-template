<?php

declare(strict_types=1);

namespace MonoPhp\Calculator\Tests\Unit;

use InvalidArgumentException;
use MonoPhp\Calculator\Calculator;
use PHPUnit\Framework\TestCase;

final class CalculatorTest extends TestCase
{
    private Calculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new Calculator();
    }

    public function test_add_returns_sum_of_two_integers(): void
    {
        $result = $this->calculator->add(5, 3);

        $this->assertSame(8, $result);
    }

    public function test_add_returns_sum_of_two_floats(): void
    {
        $result = $this->calculator->add(5.5, 3.2);

        $this->assertSame(8.7, $result);
    }

    public function test_subtract_returns_difference_of_two_integers(): void
    {
        $result = $this->calculator->subtract(10, 4);

        $this->assertSame(6, $result);
    }

    public function test_subtract_returns_difference_of_two_floats(): void
    {
        $result = $this->calculator->subtract(10.5, 4.2);

        $this->assertSame(6.3, $result);
    }

    public function test_multiply_returns_product_of_two_integers(): void
    {
        $result = $this->calculator->multiply(6, 7);

        $this->assertSame(42, $result);
    }

    public function test_multiply_returns_product_of_two_floats(): void
    {
        $result = $this->calculator->multiply(2.5, 4.0);

        $this->assertSame(10.0, $result);
    }

    public function test_divide_returns_quotient_of_two_integers(): void
    {
        $result = $this->calculator->divide(10, 2);

        $this->assertSame(5.0, $result);
    }

    public function test_divide_returns_quotient_of_two_floats(): void
    {
        $result = $this->calculator->divide(10.0, 4.0);

        $this->assertSame(2.5, $result);
    }

    public function test_divide_throws_exception_when_dividing_by_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Division by zero is not allowed');

        $this->calculator->divide(10, 0);
    }

    public function test_divide_throws_exception_when_dividing_by_zero_float(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Division by zero is not allowed');

        $this->calculator->divide(10.0, 0.0);
    }
}
