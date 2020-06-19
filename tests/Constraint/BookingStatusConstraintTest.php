<?php

declare(strict_types=1);

namespace App\Tests\Constraint;

use App\Constraint\BookingStatusConstraint;
use PHPUnit\Framework\TestCase;

class BookingStatusConstraintTest extends TestCase
{
    /**
     * @dataProvider validValues
     */
    public function testIsValid(string $value): void
    {
        $this->assertTrue(BookingStatusConstraint::isValid($value));
    }

    public function testIsInvalid(): void
    {
        $this->assertFalse(BookingStatusConstraint::isValid('abcd'));
    }

    /**
     * @see testIsValid
     */
    public function validValues(): array
    {
        return [['created'], ['complete'], ['cancelled']];
    }
}
