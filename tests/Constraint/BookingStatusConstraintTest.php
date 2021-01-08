<?php

declare(strict_types=1);

namespace App\Tests\Constraint;

use App\Constraint\BookingStatusConstraint;
use App\Tests\ProphecyTestCase;

/**
 * @coversDefaultClass \App\Constraint\BookingStatusConstraint
 */
class BookingStatusConstraintTest extends ProphecyTestCase
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
        return [['created'], ['complete'], ['cancelled'], ['rejected'], ['pending_partner_confirmation']];
    }
}
