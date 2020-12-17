<?php

declare(strict_types=1);

namespace App\Helper;

use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Money\Parser\DecimalMoneyParser;

class MoneyHelper
{
    public function create(int $amount, string $currency): Money
    {
        return new Money($amount, new Currency($currency));
    }

    public function convertToInteger(string $amount, string $currency): int
    {
        $parser = new DecimalMoneyParser(new ISOCurrencies());

        return (int) $parser->parse($amount, new Currency($currency))->getAmount();
    }

    public static function convertToDecimal(int $amount, string $currency): float
    {
        $money = new Money($amount, new Currency($currency));
        $moneyFormatter = new DecimalMoneyFormatter(new ISOCurrencies());

        return (float) $moneyFormatter->format($money);
    }

    public static function divideToInt(int $amount, string $currency, int $divisor): int
    {
        $money = new Money($amount, new Currency($currency));
        $money = $money->divide($divisor, Money::ROUND_UP);

        return (int) $money->getAmount();
    }
}
