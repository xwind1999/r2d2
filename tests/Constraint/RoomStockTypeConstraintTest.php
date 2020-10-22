<?php

declare(strict_types=1);

namespace App\Tests\Constraint;

use App\Constraint\RoomStockTypeConstraint;
use App\Tests\ProphecyTestCase;

class RoomStockTypeConstraintTest extends ProphecyTestCase
{
    /**
     * @dataProvider validValues
     */
    public function testIsValid(string $value): void
    {
        $this->assertTrue(RoomStockTypeConstraint::isValid($value));
    }

    public function testIsInvalid(): void
    {
        $this->assertFalse(RoomStockTypeConstraint::isValid('abcd'));
    }

    /**
     * @see testIsValid
     */
    public function validValues(): array
    {
        return [['allotment'], ['stock'], ['on_request']];
    }
}
