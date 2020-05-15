<?php

declare(strict_types=1);

namespace App\Command\Import;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;

class RelationshipImportCommand extends AbstractImportCommand
{
    protected static $defaultName = 'r2d2:relationship:import';

    protected const IMPORT_FIELDS = [
        'parentProduct',
        'childProduct',
        'sortOrder',
        'isEnabled',
        'relationshipType',
        'printType',
        'childCount',
        'childQuantity',
    ];

    protected function process(\Iterator $records): void
    {
        foreach ($records as $record) {
            $productRelationshipRequest = new ProductRelationshipRequest();

            $productRelationshipRequest->parentProduct = $record['parentProduct'];
            $productRelationshipRequest->childProduct = $record['childProduct'];
            $productRelationshipRequest->sortOrder = (int) $record['sortOrder'];
            $productRelationshipRequest->isEnabled = (bool) $record['isEnabled'];
            $productRelationshipRequest->relationshipType = $record['relationshipType'];
            $productRelationshipRequest->printType = $record['printType'];
            $productRelationshipRequest->childCount = (int) $record['childCount'];
            $productRelationshipRequest->childQuantity = (int) $record['childQuantity'];

            $errors = $this->validator->validate($productRelationshipRequest);
            if ($errors->count() > 0) {
                $this->logError($errors);
            }

            $this->messageBus->dispatch($productRelationshipRequest);
        }
    }
}
