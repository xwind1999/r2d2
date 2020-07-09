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
     * @Assert\Currency()
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $currencyCode;

    /**
     * @Assert\Type(type="boolean")
     * @Assert\NotNull()
     *
     * @JMS\Type("strict_boolean")
     */
    public bool $isChannelManagerEnabled;

    /**
     * @Assert\Type(type="DateTime")
     *
     * @JMS\Type("DateTime<'Y-m-d\TH:i:s.uT'>")
     */
    public ?\DateTime $partnerCeaseDate = null;

    public ?\DateTime $updatedAt = null;

    /**
     * @codeCoverageIgnore
     */
    public function getContext(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'currency_code' => $this->currencyCode,
            'is_channel_manager_enabled' => $this->isChannelManagerEnabled,
            'partner_cease_date' => $this->partnerCeaseDate ? $this->partnerCeaseDate->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null,
        ];
    }
}
