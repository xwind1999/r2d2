<?php

declare(strict_types=1);

namespace App\Tests\DBAL;

use App\DBAL\BookingStatus;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\DBAL\BookingStatus
 */
class BookingStatusTest extends TestCase
{
    /**
     * @covers ::getName
     * @covers ::convertToDatabaseValue
     * @covers \App\Constraint\BookingStatusConstraint::isValid
     *
     * @dataProvider validValues
     */
    public function testConvertToDatabaseValue(string $value)
    {
        $bookingStatusType = new BookingStatus();
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();

        $this->assertEquals($value, $bookingStatusType->convertToDatabaseValue($value, $platform));
    }

    /**
     * @covers ::getName
     * @covers ::convertToDatabaseValue
     * @covers \App\Constraint\BookingStatusConstraint::isValid
     */
    public function testConvertToDatabaseValueWillThrowException()
    {
        $value = 'eeeveeveve';
        $bookingStatusType = new BookingStatus();
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid booking_status value');
        $bookingStatusType->convertToDatabaseValue($value, $platform);
    }

    /**
     * @see testConvertToDatabaseValue
     */
    public function validValues(): array
    {
        return [['created'], ['complete'], ['cancelled']];
    }
}
