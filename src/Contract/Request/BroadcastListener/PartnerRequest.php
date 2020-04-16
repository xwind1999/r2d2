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
    public string $uuid;

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
     * @Assert\Length(min="1", max="8")
     *
     * @JMS\Type("string")
     */
    public string $status;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="3", max="3")
     *
     * @JMS\Type("string")
     */
    public string $currency;

    /**
     * @Assert\Type(type="boolean")
     * @Assert\NotNull()
     *
     * @JMS\Type("strict_boolean")
     */
    public bool $isChannelManagerActive;

    /**
     * @Assert\Type(type="DateTime")
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    public ?\DateTime $ceaseDate = null;

    public function getContext(): array
    {
        return [
            'uuid' => $this->uuid,
            'golden_id' => $this->goldenId,
            'status' => $this->status,
            'currency' => $this->currency,
            'is_channel_manager_active' => $this->isChannelManagerActive,
            'cease_date' => $this->ceaseDate,
        ];
    }
}
