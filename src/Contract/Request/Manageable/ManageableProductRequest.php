<?php

declare(strict_types=1);

namespace App\Contract\Request\Manageable;

use App\Contract\Request\BroadcastListener\PartnerRequest;
use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Event\NamedEventInterface;
use Clogger\ContextualInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ManageableProductRequest extends Event implements ContextualInterface, NamedEventInterface
{
    private const EVENT_NAME = 'Calculate Manageable Product';

    public string $boxGoldenId = '';
    public string $componentGoldenId = '';
    public string $experienceGoldenId = '';
    public string $partnerGoldenId = '';
    private ?ProductRequest $productRequest = null;
    private ?ProductRelationshipRequest $productRelationshipRequest = null;
    private ?PartnerRequest $partnerRequest = null;

    public function setProductRequest(ProductRequest $productRequest): void
    {
        $this->productRequest = $productRequest;
    }

    public function getProductRequest(): ?ProductRequest
    {
        return $this->productRequest;
    }

    public function setProductRelationshipRequest(ProductRelationshipRequest $productRelationshipRequest): void
    {
        $this->productRelationshipRequest = $productRelationshipRequest;
    }

    public function getProductRelationshipRequest(): ?ProductRelationshipRequest
    {
        return $this->productRelationshipRequest;
    }

    public function setPartnerRequest(PartnerRequest $partnerRequest): void
    {
        $this->partnerRequest = $partnerRequest;
    }

    public function getPartnerRequest(): ?PartnerRequest
    {
        return $this->partnerRequest;
    }

    public function getContext(): array
    {
        return [
            'box_golden_id' => $this->boxGoldenId,
            'component_golden_id' => $this->componentGoldenId,
            'experience_golden_id' => $this->experienceGoldenId,
            'partner_golden_id' => $this->partnerGoldenId,
        ];
    }

    public function getEventName(): string
    {
        return static::EVENT_NAME;
    }
}
