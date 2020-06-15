<?php

declare(strict_types=1);

namespace App\Contract\Request\BroadcastListener\Product;

use Clogger\ContextualInterface;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

class ListPrice implements ContextualInterface
{
    /**
     * @Assert\Type(type="integer")
     * @Assert\NotBlank
     *
     * @JMS\Type("float_to_integer")
     * @SWG\Property(example=10.50)
     */
    public int $amount;

    /**
     * @Assert\Currency()
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("currencyCode")
     */
    public string $currencyCode;

    public static function createFromAmountAndCurrencyCode(string $amount, string $currencyCode): self
    {
        $listPrice = new ListPrice();
        $listPrice->amount = (int) $amount * 100;
        $listPrice->currencyCode = $currencyCode;

        return $listPrice;
    }

    public function getContext(): array
    {
        return [
            'amount' => $this->amount,
            'currency_code' => $this->currencyCode,
        ];
    }
}
