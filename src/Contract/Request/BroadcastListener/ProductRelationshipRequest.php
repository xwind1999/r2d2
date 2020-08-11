<?php

declare(strict_types=1);

namespace App\Contract\Request\BroadcastListener;

use App\Event\NamedEventInterface;
use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;
use Clogger\ContextualInterface;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class ProductRelationshipRequest implements RequestBodyInterface, ValidatableRequest, ContextualInterface, NamedEventInterface
{
    private const EVENT_NAME = 'Product relationship broadcast';

    /**
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $parentProduct;

    /**
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $childProduct;

    /**
     * @Assert\Type(type="boolean")
     * @Assert\NotNull
     *
     * @JMS\Type("strict_boolean")
     */
    public bool $isEnabled;

    /**
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $relationshipType;

    public ?\DateTime $updatedAt = null;

    /**
     * @codeCoverageIgnore
     */
    public function getContext(): array
    {
        return [
            'parent_product' => $this->parentProduct,
            'child_product' => $this->childProduct,
            'is_enabled' => $this->isEnabled,
            'relationship_type' => $this->relationshipType,
            'updated_at' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null,
        ];
    }

    public function getEventName(): string
    {
        return static::EVENT_NAME;
    }
}
