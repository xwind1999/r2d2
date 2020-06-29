<?php

declare(strict_types=1);

namespace App\Helper\Manageable;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Manageable\ManageableProductRequest;
use Symfony\Component\Messenger\MessageBusInterface;

class ManageableProductService
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function dispatchForProduct(ProductRequest $productRequest, string $entityStatus): void
    {
        if ($entityStatus !== $productRequest->status) {
            $manageableProductRequest = new ManageableProductRequest();
            $manageableProductRequest->setProductRequest($productRequest);
            $this->messageBus->dispatch($manageableProductRequest);
        }
    }

    public function dispatchForProductRelationship(ProductRelationshipRequest $productRelationshipRequest): void
    {
        $manageableProductRequest = new ManageableProductRequest();
        $manageableProductRequest->setProductRelationshipRequest($productRelationshipRequest);
        $this->messageBus->dispatch($manageableProductRequest);
    }
}
