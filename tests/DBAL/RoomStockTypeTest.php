<?php

declare(strict_types=1);

namespace App\Tests\DBAL;

use App\DBAL\RoomStockType;
use App\Tests\ProphecyTestCase;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * @coversDefaultClass \App\DBAL\RoomStockType
 */
class RoomStockTypeTest extends ProphecyTestCase
{
    /**
     * @covers ::getName
     * @covers ::convertToDatabaseValue
     * @covers \App\Constraint\RoomStockTypeConstraint::isValid
     *
     * @dataProvider validValues
     */
    public function testConvertToDatabaseValue(string $value)
    {
        $bookingStatusType = new RoomStockType();
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();

        $this->assertEquals($value, $bookingStatusType->convertToDatabaseValue($value, $platform));
    }

    /**
     * @covers ::getName
     * @covers ::convertToDatabaseValue
     * @covers \App\Constraint\RoomStockTypeConstraint::isValid
     */
    public function testConvertToDatabaseValueWillThrowException()
    {
        $value = 'eeeveeveve';
        $bookingStatusType = new RoomStockType();
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid room_stock_type value');
        $bookingStatusType->convertToDatabaseValue($value, $platform);
    }

    /**
     * @see testConvertToDatabaseValue
     */
    public function validValues(): array
    {
        return [['allotment'], ['stock'], ['on_request']];
    }
}
