<?php

declare(strict_types=1);

namespace App\Tests\Constraint;

use App\Constraint\AvailabilityTypeConstraint;
use App\Tests\ProphecyTestCase;

class AvailabilityTypeConstraintTest extends ProphecyTestCase
{
    /**
     * @dataProvider validValues
     */
    public function testIsValid(string $value): void
    {
        $this->assertTrue(AvailabilityTypeConstraint::isValid($value));
    }

    public function testIsInvalid(): void
    {
        $this->assertFalse(AvailabilityTypeConstraint::isValid('abcd'));
    }

    public function testIsInvalidWithNull(): void
    {
        $this->assertFalse(AvailabilityTypeConstraint::isValid(null));
    }

    /**
     * @see testIsValid
     */
    public function validValues(): array
    {
        return [['instant'], ['on-request']];
    }
}
