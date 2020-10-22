<?php

declare(strict_types=1);

namespace App\Tests\DBAL;

use App\DBAL\PartnerStatus;
use App\Tests\ProphecyTestCase;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * @coversDefaultClass \App\DBAL\PartnerStatus
 */
class PartnerStatusTest extends ProphecyTestCase
{
    /**
     * @covers ::getName
     * @covers ::convertToDatabaseValue
     * @covers \App\Constraint\PartnerStatusConstraint::isValid
     *
     * @dataProvider validValues
     */
    public function testConvertToDatabaseValue(string $value)
    {
        $bookingStatusType = new PartnerStatus();
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();

        $this->assertEquals($value, $bookingStatusType->convertToDatabaseValue($value, $platform));
    }

    /**
     * @covers ::getName
     * @covers ::convertToDatabaseValue
     * @covers \App\Constraint\PartnerStatusConstraint::isValid
     */
    public function testConvertToDatabaseValueWillThrowException()
    {
        $value = 'eeeveeveve';
        $bookingStatusType = new PartnerStatus();
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid partner_status value');
        $bookingStatusType->convertToDatabaseValue($value, $platform);
    }

    /**
     * @see testConvertToDatabaseValue
     */
    public function validValues(): array
    {
        return [
            ['prospect'],
            ['new partner'],
            ['partner'],
            ['inactive partner'],
            ['former partner'],
            ['blacklist'],
            ['winback'],
            ['ceased'],
        ];
    }
}
