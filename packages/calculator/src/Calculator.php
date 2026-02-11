<?php

declare(strict_types=1);

namespace MonoPhp\Calculator;

/**
 * Simple Calculator Class.
 *
 * Provides basic arithmetic operations for testing the monorepo structure.
 */
class Calculator
{
    /**
     * Add two numbers.
     *
     * @param int|float $a First number
     * @param int|float $b Second number
     * @return int|float Sum of the two numbers
     */
    public function add(int|float $a, int|float $b): int|float
    {
        return $a + $b;
    }

    /**
     * Subtract two numbers.
     *
     * @param int|float $a First number
     * @param int|float $b Second number
     * @return int|float Difference of the two numbers
     */
    public function subtract(int|float $a, int|float $b): int|float
    {
        return $a - $b;
    }

    /**
     * Multiply two numbers.
     *
     * @param int|float $a First number
     * @param int|float $b Second number
     * @return int|float Product of the two numbers
     */
    public function multiply(int|float $a, int|float $b): int|float
    {
        return $a * $b;
    }

    /**
     * Divide two numbers.
     *
     * @param int|float $a First number (dividend)
     * @param int|float $b Second number (divisor)
     * @return float Quotient of the two numbers
     * @throws \InvalidArgumentException If divisor is zero
     */
    public function divide(int|float $a, int|float $b): float
    {
        if ($b === 0 || $b === 0.0) {
            throw new \InvalidArgumentException('Division by zero is not allowed');
        }

        return $a / $b;
    }
}
