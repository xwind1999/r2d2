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
        'isEnabled',
        'relationshipType',
        'updatedAt',
    ];

    protected function process(\Iterator $records): void
    {
        foreach ($records as $record) {
            $productRelationshipRequest = new ProductRelationshipRequest();

            $productRelationshipRequest->parentProduct = $record['parentProduct'];
            $productRelationshipRequest->childProduct = $record['childProduct'];
            $productRelationshipRequest->isEnabled = (bool) $record['isEnabled'];
            $productRelationshipRequest->relationshipType = $record['relationshipType'];
            if (!empty($record['updatedAt'])) {
                $productRelationshipRequest->updatedAt = new \DateTime($record['updatedAt']);
            }

            $errors = $this->validator->validate($productRelationshipRequest);
            if ($errors->count() > 0) {
                $this->logError($errors);
            }

            $this->messageBus->dispatch($productRelationshipRequest);
        }
    }
}
