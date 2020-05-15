<?php

declare(strict_types=1);

namespace App\Helper;

use Money\Currency;
use Money\Money;

class MoneyHelper
{
    public function create(int $amount, string $currency): Money
    {
        return new Money($amount, new Currency($currency));
    }
}
