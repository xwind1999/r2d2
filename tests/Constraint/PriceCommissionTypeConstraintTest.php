<?php

declare(strict_types=1);

namespace App\Tests\Constraint;

use App\Constraint\PriceCommissionTypeConstraint;
use PHPUnit\Framework\TestCase;

class PriceCommissionTypeConstraintTest extends TestCase
{
    /**
     * @dataProvider validValues
     */
    public function testIsValid(string $value): void
    {
        $this->assertTrue(PriceCommissionTypeConstraint::isValid($value));
    }

    public function testIsInvalid(): void
    {
        $this->assertFalse(PriceCommissionTypeConstraint::isValid('abcd'));
    }

    /**
     * @see testIsValid
     */
    public function validValues(): array
    {
        return [['percentage'], ['amount']];
    }
}
