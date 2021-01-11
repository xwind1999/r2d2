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
     * @dataProvider validValuesProvider
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
    public function validValuesProvider(): array
    {
        return [
            ['created'],
            ['complete'],
            ['cancelled'],
            ['rejected'],
            ['pending_partner_confirmation'],
        ];
    }

    /**
     * @dataProvider validOnRequestValuesProvider
     */
    public function testIsAnOnRequestStatus(string $value): void
    {
        $this->assertTrue(BookingStatusConstraint::isAnOnRequestStatus($value));
    }

    public function testIsInvalidOnRequestStatus(): void
    {
        $this->assertFalse(BookingStatusConstraint::isAnOnRequestStatus('cancelled'));
    }

    /**
     * @see testIsAnOnRequestStatus
     */
    public function validOnRequestValuesProvider(): array
    {
        return [
            ['rejected'],
            ['pending_partner_confirmation'],
        ];
    }

    public function testGetValidValuesForUpdate(): void
    {
        $expected = [
            'complete',
            'cancelled',
            'rejected',
            'pending_partner_confirmation',
        ];
        $this->assertEquals($expected, BookingStatusConstraint::getValidValuesForUpdate());
    }
}
