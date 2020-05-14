<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Helper\MoneyHelper;
use Money\Money;
use PHPUnit\Framework\TestCase;

class MoneyHelperTest extends TestCase
{
    public function testCreate()
    {
        $moneyHelper = new MoneyHelper();
        $money = $moneyHelper->create(400, 'EUR');
        $this->assertInstanceOf(Money::class, $money);
        $this->assertEquals(400, $money->getAmount());
        $this->assertEquals('EUR', $money->getCurrency()->getCode());
    }
}
