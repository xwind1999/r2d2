<?php

declare(strict_types=1);

namespace App\Tests\Constraint;

use App\Constraint\BookingChannelConstraint;
use App\Tests\ProphecyTestCase;

/**
 * @coversDefaultClass \App\Constraint\BookingChannelConstraint
 */
class BookingChannelConstraintTest extends ProphecyTestCase
{
    /**
     * @dataProvider validValues
     */
    public function testIsValid(string $value): void
    {
        self::assertTrue(BookingChannelConstraint::isValid($value));
    }

    public function testIsInvalid(): void
    {
        self::assertFalse(BookingChannelConstraint::isValid('abcd'));
    }

    public function testGetValidValues()
    {
        $validValues = ['customer', 'jarvis-booking', 'partner', null];

        self::assertEquals($validValues, BookingChannelConstraint::getValidValues());
    }

    /**
     * @see testIsValid
     */
    public function validValues(): array
    {
        return [['partner'], ['customer'], ['jarvis-booking']];
    }
}
