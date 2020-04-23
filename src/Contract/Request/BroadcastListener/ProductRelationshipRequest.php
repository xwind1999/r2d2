<?php

declare(strict_types=1);

namespace App\Contract\Request\BroadcastListener;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;
use Clogger\ContextualInterface;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class ProductRelationshipRequest implements RequestBodyInterface, ValidatableRequest, ContextualInterface
{
    /**
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("parentProduct")
     */
    public string $parentProduct;

    /**
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("childProduct")
     */
    public string $childProduct;

    /**
     * @Assert\Type(type="integer")
     * @Assert\PositiveOrZero
     * @Assert\NotBlank
     *
     * @JMS\Type("strict_integer")
     * @JMS\SerializedName("sortOrder")
     */
    public int $sortOrder;

    /**
     * @Assert\Type(type="boolean")
     * @Assert\NotNull
     *
     * @JMS\Type("strict_boolean")
     * @JMS\SerializedName("isEnabled")
     */
    public bool $isEnabled;

    /**
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("relationshipType")
     */
    public string $relationshipType;

    /**
     * @Assert\Type(type="string")
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("printType")
     */
    public string $printType = '';

    /**
     * @Assert\Type(type="integer")
     *
     * @JMS\Type("strict_integer")
     * @JMS\SerializedName("childCount")
     */
    public int $childCount = 0;

    /**
     * @Assert\Type(type="integer")
     *
     * @JMS\Type("strict_integer")
     * @JMS\SerializedName("childQuantity")
     */
    public int $childQuantity = 0;

    public function getContext(): array
    {
        return [
            'parent_product' => $this->parentProduct,
            'child_product' => $this->childProduct,
            'sort_order' => $this->sortOrder,
            'is_enabled' => $this->isEnabled,
            'relationship_type' => $this->relationshipType,
            'print_type' => $this->printType,
            'child_count' => $this->childCount,
            'child_quantity' => $this->childQuantity,
        ];
    }
}
