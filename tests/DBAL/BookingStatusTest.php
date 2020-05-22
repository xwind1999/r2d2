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
     */
    public function testConvertToDatabaseValue()
    {
        $status = 'created';
        $bookingStatusType = new BookingStatus();
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();

        $this->assertEquals($status, $bookingStatusType->convertToDatabaseValue($status, $platform));
    }

    /**
     * @covers ::getName
     * @covers ::convertToDatabaseValue
     */
    public function testConvertToDatabaseValueWillThrowException()
    {
        $status = 'eeeveeveve';
        $bookingStatusType = new BookingStatus();
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid booking_status value');
        $bookingStatusType->convertToDatabaseValue($status, $platform);
    }
}
