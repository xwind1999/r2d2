<?php

declare(strict_types=1);

namespace App\Tests\DBAL;

use App\DBAL\DateTimeMillisecondsType;
use App\Tests\ProphecyTestCase;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \App\DBAL\DateTimeMillisecondsType
 */
class DateTimeMillisecondsTypeTest extends ProphecyTestCase
{
    private DateTimeMillisecondsType $dateTimeMillisecondsType;

    /**
     * @var AbstractPlatform|ObjectProphecy
     */
    private $abstractPlatform;

    public function setUp(): void
    {
        $this->dateTimeMillisecondsType = new DateTimeMillisecondsType();
        $this->abstractPlatform = $this->prophesize(AbstractPlatform::class);
    }

    /**
     * @covers ::convertToDatabaseValue
     *
     * @dataProvider dataProvider
     */
    public function testConvertToDatabaseValue($value, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->dateTimeMillisecondsType->convertToDatabaseValue($value, $this->abstractPlatform->reveal())
        );
    }

    /**
     * @covers ::convertToDatabaseValue
     */
    public function testConvertToDatabaseValueWillThrowException()
    {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage(
            'Could not convert PHP value \'aaaa\' of type \'string\' to type \'datetime_milliseconds\'. '.
            'Expected one of the following types: null, DateTime'
        );

        $this->dateTimeMillisecondsType->convertToDatabaseValue('aaaa', $this->abstractPlatform->reveal());
    }

    /**
     * @covers ::convertToPHPValue
     *
     * @dataProvider dataProvider
     */
    public function testConvertToPHPValue($expected, $value)
    {
        $this->assertEquals(
            $expected,
            $this->dateTimeMillisecondsType->convertToPHPValue($value, $this->abstractPlatform->reveal())
        );
    }

    /**
     * @covers ::convertToPHPValue
     */
    public function testConvertToPHPValueWithWeirdFormat()
    {
        $dateTime = '2020-01-01 00:00:00';
        $this->assertEquals(
            new \DateTime('2020-01-01 00:00:00.000000'),
            $this->dateTimeMillisecondsType->convertToPHPValue($dateTime, $this->abstractPlatform->reveal())
        );
    }

    /**
     * @covers ::convertToPHPValue
     */
    public function testConvertToPHPValueWillThrowException()
    {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage(
            'Could not convert database value "aaaa" to Doctrine Type datetime_milliseconds. '.
            'Expected format: Y-m-d H:i:s.u'
        );

        $this->dateTimeMillisecondsType->convertToPHPValue('aaaa', $this->abstractPlatform->reveal());
    }

    /**
     * @covers ::requiresSQLCommentHint
     */
    public function testRequiresSQLCommentHint()
    {
        $this->assertTrue($this->dateTimeMillisecondsType->requiresSQLCommentHint($this->abstractPlatform->reveal()));
    }

    /**
     * @covers ::getName
     */
    public function testGetName()
    {
        $this->assertEquals('datetime_milliseconds', $this->dateTimeMillisecondsType->getName());
    }

    /**
     * @covers ::getSQLDeclaration
     */
    public function testGetSQLDeclaration()
    {
        $sqlDeclaration = $this->dateTimeMillisecondsType->getSQLDeclaration([], $this->abstractPlatform->reveal());
        $this->assertEquals('DATETIME(3)', $sqlDeclaration);
    }

    public function dataProvider(): \Generator
    {
        yield [null, null];

        $dt = '2020-01-01 00:00:00.123000';
        $dateTime = new \DateTime($dt);

        yield [$dateTime, $dt];
    }
}
