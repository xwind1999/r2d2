<?php

declare(strict_types=1);

namespace App\Contract\Request\Internal\Box;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class BoxCreateRequest implements RequestBodyInterface, ValidatableRequest
{
    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="45")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $goldenId;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="3", max="3")
     *
     * @JMS\Type("string")
     */
    public ?string $brand;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="2", max="2")
     *
     * @JMS\Type("string")
     */
    public ?string $country;

    /**
     * @Assert\Type(type="string")
     * @Assert\Choice(choices=\App\Constraint\ProductStatusConstraint::VALID_VALUES)
     *
     * @JMS\Type("string")
     */
    public string $status;

    /**
     * @Assert\Currency()
     *
     * @JMS\Type("string")
     */
    public ?string $currency = null;
}
