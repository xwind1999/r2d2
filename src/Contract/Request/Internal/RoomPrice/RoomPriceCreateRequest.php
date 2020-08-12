<?php

declare(strict_types=1);

namespace App\Contract\Request\Internal\RoomPrice;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

class RoomPriceCreateRequest implements RequestBodyInterface, ValidatableRequest
{
    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="45")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $componentGoldenId;

    /**
     * @Assert\Type(type="DateTime")
     * @Assert\NotBlank
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    public \DateTime $date;

    /**
     * @Assert\Type(type="integer")
     * @Assert\PositiveOrZero
     * @Assert\NotBlank
     *
     * @JMS\Type("strict_integer")
     * @SWG\Property(type="integer", example=10)
     */
    public int $price;
}
