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

    /**
     * @dataProvider isInstantTypeProvider
     */
    public function testIsInstantType(string $value): void
    {
        $this->assertTrue(RoomStockTypeConstraint::isInstantType($value));
    }

    /**
     * @see testIsInstantType
     */
    public function isInstantTypeProvider(): array
    {
        return [['allotment'], ['stock']];
    }

    public function testIsInstantTypeFalse(): void
    {
        $this->assertFalse(RoomStockTypeConstraint::isInstantType('on-request'));
    }

    public function testIsInstantTypeNull(): void
    {
        $this->assertFalse(RoomStockTypeConstraint::isInstantType(null));
    }

    public function testIsOnRequestType(): void
    {
        $this->assertTrue(RoomStockTypeConstraint::isOnRequestType('on_request'));
    }

    public function testIsOnRequestTypeFalse(): void
    {
        $this->assertFalse(RoomStockTypeConstraint::isOnRequestType('instant'));
    }

    public function testIsOnRequestTypeNull(): void
    {
        $this->assertFalse(RoomStockTypeConstraint::isOnRequestType(null));
    }
}
