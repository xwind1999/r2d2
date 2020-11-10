<?php

declare(strict_types=1);

namespace App\Command\Import;

use App\Contract\Request\BroadcastListener\Common\Price;
use App\Contract\Request\BroadcastListener\Product\Product;
use App\Contract\Request\BroadcastListener\RoomPriceRequest;

class RoomPriceImportCommand extends AbstractImportCommand
{
    protected static $defaultName = 'r2d2:room-price:import';

    protected const IMPORT_FIELDS = [
        'product.id',
        'dateFrom',
        'dateTo',
        'price.amount',
        'price.currencyCode',
        'updatedAt',
    ];

    protected function configure(): void
    {
        parent::configure();
    }

    public function process(\Iterator $records): void
    {
        foreach ($records as $record) {
            $roomPriceRequest = new RoomPriceRequest();

            $product = new Product();
            $product->id = $record['product.id'];

            $roomPriceRequest->product = $product;
            $roomPriceRequest->dateFrom = new \DateTime($record['dateFrom']);
            $roomPriceRequest->dateTo = new \DateTime($record['dateTo']);

            $roomPriceRequest->price = Price::fromAmountAndCurrencyCode($record['price.amount'], $record['price.currencyCode']);

            if (!empty($record['updatedAt'])) {
                $roomPriceRequest->updatedAt = new \DateTime($record['updatedAt']);
            }

            $errors = $this->validator->validate($roomPriceRequest);
            if ($errors->count() > 0) {
                $this->logError($errors);
            }

            $this->messageBus->dispatch($roomPriceRequest);
        }
    }
}
