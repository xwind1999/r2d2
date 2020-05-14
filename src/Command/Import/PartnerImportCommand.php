<?php

declare(strict_types=1);

namespace App\Command\Import;

use App\Contract\Request\BroadcastListener\PartnerRequest;

class PartnerImportCommand extends AbstractImportCommand
{
    protected static $defaultName = 'r2d2:partner:import';

    protected const IMPORT_FIELDS = [
        'Account_URN__c',
        'Type',
        'CurrencyIsoCode',
        'CeaseDate__c',
        'Channel_Manager_Active__c',
    ];

    protected function configure(): void
    {
        parent::configure();
    }

    public function process(\Iterator $records): void
    {
        foreach ($records as $record) {
            $partnerRequest = new PartnerRequest();

            $partnerRequest->id = $record['Account_URN__c'];
            $partnerRequest->status = $record['Type'];
            $partnerRequest->currencyCode = $record['CurrencyIsoCode'];
            $partnerRequest->isChannelManagerEnabled = (bool) ($record['Channel_Manager_Active__c']);
            $partnerRequest->partnerCeaseDate = new \DateTime($record['CeaseDate__c']);

            $errors = $this->validator->validate($partnerRequest);
            if ($errors->count() > 0) {
                $this->logError($errors);
            }

            $this->messageBus->dispatch($partnerRequest);
        }
    }
}
