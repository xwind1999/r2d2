<?php

declare(strict_types=1);

namespace App\Tests\Constraint;

use App\Constraint\RoomStockTypeConstraint;
use PHPUnit\Framework\TestCase;

class RoomStockTypeConstraintTest extends TestCase
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
