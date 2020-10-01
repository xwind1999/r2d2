<?php

declare(strict_types=1);

namespace App\Command\Import;

use App\Contract\Request\BroadcastListener\PartnerRequest;

class PartnerImportCommand extends AbstractImportCommand
{
    protected static $defaultName = 'r2d2:partner:import';

    protected const IMPORT_FIELDS = [
        'id',
        'type',
        'currencyCode',
        'partnerCeaseDate',
        'updatedAt',
    ];

    protected function configure(): void
    {
        parent::configure();
    }

    protected function process(\Iterator $records): void
    {
        foreach ($records as $record) {
            $partnerRequest = new PartnerRequest();

            $partnerRequest->id = $record['id'];
            $partnerRequest->status = $record['type'];
            $partnerRequest->currencyCode = $record['currencyCode'];

            if (!empty($record['partnerCeaseDate'])) {
                $partnerRequest->partnerCeaseDate = new \DateTime($record['partnerCeaseDate']);
            }

            if (!empty($record['updatedAt'])) {
                $partnerRequest->updatedAt = new \DateTime($record['updatedAt']);
            }

            $errors = $this->validator->validate($partnerRequest);
            if ($errors->count() > 0) {
                $this->logError($errors);
            }

            $this->messageBus->dispatch($partnerRequest);
        }
    }
}
