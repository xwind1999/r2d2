<?php

declare(strict_types=1);

namespace App\Tests\DBAL;

use App\DBAL\ProductStatus;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\DBAL\ProductStatus
 */
class ProductStatusTest extends TestCase
{
    /**
     * @covers ::getName
     * @covers ::convertToDatabaseValue
     * @covers \App\Constraint\ProductStatusConstraint::isValid
     *
     * @dataProvider validValues
     */
    public function testConvertToDatabaseValue(string $value)
    {
        $bookingStatusType = new ProductStatus();
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();

        $this->assertEquals($value, $bookingStatusType->convertToDatabaseValue($value, $platform));
    }

    /**
     * @covers ::getName
     * @covers ::convertToDatabaseValue
     * @covers \App\Constraint\ProductStatusConstraint::isValid
     */
    public function testConvertToDatabaseValueWillThrowException()
    {
        $value = 'eeeveeveve';
        $bookingStatusType = new ProductStatus();
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid product_status value');
        $bookingStatusType->convertToDatabaseValue($value, $platform);
    }

    /**
     * @see testConvertToDatabaseValue
     */
    public function validValues(): array
    {
        return [['prospect'], ['production'], ['live'], ['obsolete'], ['active'], ['inactive'], ['redeemable'], ['ready']];
    }
}
