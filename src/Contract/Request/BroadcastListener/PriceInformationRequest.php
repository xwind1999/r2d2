<?php

declare(strict_types=1);

namespace App\Contract\Request\BroadcastListener;

use App\Contract\Request\BroadcastListener\Common\Price;
use App\Contract\Request\BroadcastListener\Product\Product;
use App\Event\NamedEventInterface;
use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;
use Clogger\ContextualInterface;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

class PriceInformationRequest implements RequestBodyInterface, ValidatableRequest, ContextualInterface, NamedEventInterface
{
    private const EVENT_NAME = 'Price broadcast';

    /**
     * @Assert\Type(type="App\Contract\Request\BroadcastListener\Product\Product")
     * @Assert\NotBlank
     * @Assert\Valid
     *
     * @JMS\Type("App\Contract\Request\BroadcastListener\Product\Product")
     */
    public Product $product;

    /**
     * @Assert\Type(type="App\Contract\Request\BroadcastListener\Common\Price")
     * @Assert\Valid
     *
     * @JMS\Type("App\Contract\Request\BroadcastListener\Common\Price")
     */
    public ?Price $averageValue = null;

    /**
     * @Assert\Type(type="string")
     * @Assert\Choice(choices=\App\Constraint\PriceCommissionTypeConstraint::VALID_VALUES)
     *
     * @JMS\Type("string")
     * @SWG\Property(example="amount")
     */
    public ?string $averageCommissionType = null;

    /**
     * @Assert\Type(type="string")
     *
     * @JMS\Type("string")
     * @SWG\Property(example="5.20")
     */
    public ?string $averageCommission = null;

    public ?\DateTime $updatedAt = null;

    public function getContext(): array
    {
        return [
            'product' => $this->product->getContext(),
            'average_value' => $this->averageValue ? $this->averageValue->amount : '',
            'average_commission_type' => $this->averageCommissionType,
            'average_commission' => $this->averageCommission,
            'updated_at' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null,
        ];
    }

    public function getEventName(): string
    {
        return static::EVENT_NAME;
    }
}
