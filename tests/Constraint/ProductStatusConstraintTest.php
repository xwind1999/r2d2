<?php

declare(strict_types=1);

namespace App\Tests\Constraint;

use App\Constraint\ProductStatusConstraint;
use App\Tests\ProphecyTestCase;

class ProductStatusConstraintTest extends ProphecyTestCase
{
    /**
     * @dataProvider validValues
     */
    public function testIsValid(string $value): void
    {
        $this->assertTrue(ProductStatusConstraint::isValid($value));
    }

    public function testIsInvalid(): void
    {
        $this->assertFalse(ProductStatusConstraint::isValid('abcd'));
    }

    /**
     * @see testIsValid
     */
    public function validValues(): array
    {
        return [['prospect'], ['production'], ['live'], ['obsolete'], ['active'], ['inactive'], ['redeemable'], ['ready']];
    }
}
