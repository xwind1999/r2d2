<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Helper\MoneyHelper;
use App\Tests\ProphecyTestCase;
use Money\Money;

class MoneyHelperTest extends ProphecyTestCase
{
    private MoneyHelper $moneyHelper;

    public function setUp(): void
    {
        $this->moneyHelper = new MoneyHelper();
    }

    public function testCreate()
    {
        $money = $this->moneyHelper->create(400, 'EUR');
        $this->assertInstanceOf(Money::class, $money);
        $this->assertEquals(400, $money->getAmount());
        $this->assertEquals('EUR', $money->getCurrency()->getCode());
    }

    public function testConvertToInteger()
    {
        $amount = '30.99';
        $currency = 'EUR';
        $integer = $this->moneyHelper->convertToInteger($amount, $currency);
        $this->assertSame(3099, $integer);
    }

    public function testConvertToDecimal()
    {
        $amount = 100;
        $currency = 'EUR';
        $decimal = MoneyHelper::convertToDecimal($amount, $currency);
        $this->assertSame(1.00, $decimal);
    }

    public function testConvertToDecimalNotEven()
    {
        $amount = 101;
        $currency = 'EUR';
        $decimal = MoneyHelper::convertToDecimal($amount, $currency);
        $this->assertSame(1.01, $decimal);
    }

    public function testDivideToInt()
    {
        $amount = 300;
        $currency = 'EUR';
        $integer = MoneyHelper::divideToInt($amount, $currency, 3);
        $this->assertSame(100, $integer);
    }

    public function testDivideToIntNotEven()
    {
        $amount = 301;
        $currency = 'EUR';
        $integer = MoneyHelper::divideToInt($amount, $currency, 3);
        $this->assertSame(101, $integer);
    }
}
