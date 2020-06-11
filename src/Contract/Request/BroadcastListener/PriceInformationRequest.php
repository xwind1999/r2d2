<?php

declare(strict_types=1);

namespace App\Contract\Request\BroadcastListener;

use App\Contract\Request\BroadcastListener\PriceInformation\Price;
use App\Contract\Request\BroadcastListener\PriceInformation\Product;
use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;
use Clogger\ContextualInterface;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

class PriceInformationRequest implements RequestBodyInterface, ValidatableRequest, ContextualInterface
{
    /**
     * @Assert\Type(type="App\Contract\Request\BroadcastListener\PriceInformation\Product")
     * @Assert\NotBlank
     * @Assert\Valid
     *
     * @JMS\Type("App\Contract\Request\BroadcastListener\PriceInformation\Product")
     */
    public Product $product;

    /**
     * @Assert\Type(type="App\Contract\Request\BroadcastListener\PriceInformation\Price")
     * @Assert\Valid
     *
     * @JMS\Type("App\Contract\Request\BroadcastListener\PriceInformation\Price")
     */
    public ?Price $averageValue = null;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="10")
     *
     * @JMS\Type("string")
     * @SWG\Property(example="amount")
     */
    public ?string $averageCommissionType = null;

    /**
     * @Assert\Type(type="integer")
     *
     * @JMS\Type("float_to_integer")
     * @SWG\Property(example=5.20)
     */
    public ?int $averageCommission = null;

    public function getContext(): array
    {
        return [
            'product' => $this->product,
            'average_value' => $this->averageValue ? $this->averageValue->amount : '',
            'average_commission_type' => $this->averageCommissionType,
            'average_commission' => $this->averageCommission,
        ];
    }
}
