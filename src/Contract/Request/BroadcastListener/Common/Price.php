<?php

declare(strict_types=1);

namespace App\Contract\Request\BroadcastListener\Common;

use Clogger\ContextualInterface;
use JMS\Serializer\Annotation as JMS;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

class Price implements ContextualInterface
{
    /**
     * @Assert\Type(type="integer")
     * @Assert\NotBlank()
     *
     * @JMS\Type("strict_integer")
     * @OA\Property(example=10.50, type="float")
     */
    public int $amount;

    /**
     * @Assert\Currency()
     * @Assert\NotBlank()
     *
     * @JMS\Type("string")
     * @OA\Property(example="EUR")
     */
    public string $currencyCode;

    public static function fromAmountAndCurrencyCode(int $amount, string $currencyCode): self
    {
        $price = new self();
        $price->amount = $amount;
        $price->currencyCode = $currencyCode;

        return $price;
    }

    public function getContext(): array
    {
        return [
            'amount' => $this->amount,
            'currency_code' => $this->currencyCode,
        ];
    }
}
