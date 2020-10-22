<?php

declare(strict_types=1);

namespace App\Tests\Constraint;

use App\Constraint\ProductDurationUnitConstraint;
use App\Tests\ProphecyTestCase;

class ProductDurationUnitConstraintTest extends ProphecyTestCase
{
    /**
     * @dataProvider validValues
     */
    public function testIsValid(string $value): void
    {
        $this->assertTrue(ProductDurationUnitConstraint::isValid($value));
    }

    public function testIsInvalid(): void
    {
        $this->assertFalse(ProductDurationUnitConstraint::isValid('abcd'));
    }

    /**
     * @see testIsValid
     */
    public function validValues(): array
    {
        return [['Minutes'], ['Hours'], ['Days'], ['Nights']];
    }
}
