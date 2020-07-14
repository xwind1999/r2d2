<?php

declare(strict_types=1);

namespace App\Helper\Manageable;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Manageable\ManageableProductRequest;
use App\Entity\Box;
use App\Entity\Component;
use App\Entity\Experience;
use Symfony\Component\Messenger\MessageBusInterface;

class ManageableProductService
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function dispatchForProductRelationship(ProductRelationshipRequest $productRelationshipRequest): void
    {
        $manageableProductRequest = new ManageableProductRequest();
        $manageableProductRequest->setProductRelationshipRequest($productRelationshipRequest);
        $this->messageBus->dispatch($manageableProductRequest);
    }

    public function dispatchForBox(ProductRequest $productRequest, Box $previousBox): void
    {
        if (isset($previousBox->status) && $previousBox->status !== $productRequest->status) {
            $this->dispatchForProduct($productRequest);
        }
    }

    public function dispatchForComponent(ProductRequest $productRequest, Component $previousComponent): void
    {
        if (
            (isset($previousComponent->status) && $previousComponent->status !== $productRequest->status)
            || (isset($previousComponent->isReservable) && $previousComponent->isReservable !== $productRequest->isReservable)
        ) {
            $this->dispatchForProduct($productRequest);
        }
    }

    public function dispatchForExperience(ProductRequest $productRequest, Experience $previousExperience): void
    {
        if (isset($previousExperience->status) && $previousExperience->status !== $productRequest->status) {
            $this->dispatchForProduct($productRequest);
        }
    }

    private function dispatchForProduct(ProductRequest $productRequest): void
    {
        $manageableProductRequest = new ManageableProductRequest();
        $manageableProductRequest->setProductRequest($productRequest);
        $this->messageBus->dispatch($manageableProductRequest);
    }
}
