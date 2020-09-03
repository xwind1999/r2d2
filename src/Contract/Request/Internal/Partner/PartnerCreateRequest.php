<?php

declare(strict_types=1);

namespace App\Contract\Request\Internal\Partner;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class PartnerCreateRequest implements RequestBodyInterface, ValidatableRequest
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
     * @Assert\Choice(choices=\App\Constraint\PartnerStatusConstraint::VALID_VALUES)
     *
     * @JMS\Type("string")
     */
    public string $status;

    /**
     * @Assert\Currency()
     *
     * @JMS\Type("string")
     */
    public string $currency;

    /**
     * @Assert\Type(type="DateTime")
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    public ?\DateTime $ceaseDate = null;
}
