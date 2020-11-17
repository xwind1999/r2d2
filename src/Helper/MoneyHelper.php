<?php

declare(strict_types=1);

namespace App\Helper;

use Money\Currencies\ISOCurrencies;
use Money\Currency;
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
}
