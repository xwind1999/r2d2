<?php

declare(strict_types=1);

namespace App\Contract\Request\BroadcastListener;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;
use Clogger\ContextualInterface;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class PartnerRequest implements RequestBodyInterface, ValidatableRequest, ContextualInterface
{
    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="45")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $id;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="16")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $status;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="3", max="3")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("currencyCode")
     */
    public string $currencyCode;

    /**
     * @Assert\Type(type="boolean")
     * @Assert\NotNull()
     *
     * @JMS\Type("strict_boolean")
     * @JMS\SerializedName("isChannelManagerEnabled")
     */
    public bool $isChannelManagerEnabled;

    /**
     * @Assert\Type(type="DateTime")
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\SerializedName("partnerCeaseDate")
     */
    public ?\DateTime $partnerCeaseDate = null;

    public function getContext(): array
    {
        return [
            'uuid' => $this->uuid,
            'id' => $this->id,
            'status' => $this->status,
            'currency_code' => $this->currencyCode,
            'is_channel_manager_enabled' => $this->isChannelManagerEnabled,
            'partner_cease_date' => $this->partnerCeaseDate,
        ];
    }
}
