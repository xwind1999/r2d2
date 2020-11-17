<?php

declare(strict_types=1);

namespace App\Command\Import;

use App\Contract\Request\BroadcastListener\Common\Price;
use App\Contract\Request\BroadcastListener\Product\Brand;
use App\Contract\Request\BroadcastListener\Product\Country;
use App\Contract\Request\BroadcastListener\Product\Partner;
use App\Contract\Request\BroadcastListener\Product\Universe;
use App\Contract\Request\BroadcastListener\ProductRequest;

abstract class AbstractProductImportCommand extends AbstractImportCommand
{
    protected static $defaultName = 'r2d2:product:import';

    public function process(\Iterator $records): void
    {
        foreach ($records as $record) {
            $productRequest = $this->createProductRequest($record);
            $errors = $this->validator->validate($productRequest);
            if ($errors->count() > 0) {
                $this->logError($errors);
            }

            $this->messageBus->dispatch($productRequest);
        }
    }

    private function createProductRequest(array $product): ProductRequest
    {
        $productRequest = new ProductRequest();
        $productRequest->id = $product['id'];
        $productRequest->name = $product['name'] ?? '';
        $productRequest->status = $product['status'];
        $productRequest->type = $product['type'];
        $productRequest->description = $product['description'] ?? null;

        if (!empty($product['universe.id'])) {
            $productRequest->universe = Universe::create($product['universe.id']);
        }

        if (!empty($product['roomStockType'])) {
            $productRequest->roomStockType = $product['roomStockType'];
        }

        if (!empty($product['productDurationUnit'])) {
            $productRequest->productDurationUnit = $product['productDurationUnit'];
        }

        if (!empty($product['productPeopleNumber'])) {
            $productRequest->productPeopleNumber = (int) $product['productPeopleNumber'];
        }

        if (!empty($product['stockAllotment'])) {
            $productRequest->stockAllotment = (int) $product['stockAllotment'];
        }

        if (!empty($product['productDuration'])) {
            $productRequest->productDuration = (int) $product['productDuration'];
        }

        if (!empty($product['sellableBrand'])) {
            $productRequest->sellableBrand = Brand::create($product['sellableBrand']);
        }

        if (!empty($product['sellableCountry'])) {
            $productRequest->sellableCountry = Country::create($product['sellableCountry']);
        }

        if (!empty($product['updatedAt'])) {
            $productRequest->updatedAt = new \DateTime($product['updatedAt']);
        }

        if (!empty($product['partner'])) {
            $productRequest->partner = Partner::create($product['partner']);
        }

        if (!empty($product['isSellable'])) {
            $productRequest->isSellable = (bool) $product['isSellable'];
        }

        if (!empty($product['isReservable'])) {
            $productRequest->isReservable = (bool) $product['isReservable'];
        }

        if (!empty($product['listPrice.amount']) && !empty($product['listPrice.currencyCode'])) {
            $amount = $this->moneyHelper->convertToInteger((string) $product['listPrice.amount'], $product['listPrice.currencyCode']);
            $productRequest->listPrice = Price::fromAmountAndCurrencyCode(
                $amount,
                $product['listPrice.currencyCode']
            );
        }

        return $productRequest;
    }
}
