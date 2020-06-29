<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\BroadcastListener\PriceInformationRequest;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Internal\Box\BoxCreateRequest;
use App\Contract\Request\Internal\Box\BoxUpdateRequest;
use App\Entity\Box;
use App\Exception\Manager\Box\OutdatedBoxException;
use App\Exception\Repository\BoxNotFoundException;
use App\Exception\Repository\EntityNotFoundException;
use App\Helper\Manageable\ManageableProductService;
use App\Repository\BoxRepository;

class BoxManager
{
    private BoxRepository $repository;
    private ManageableProductService $manageableProductService;

    public function __construct(BoxRepository $repository, ManageableProductService $manageableProductService)
    {
        $this->repository = $repository;
        $this->manageableProductService = $manageableProductService;
    }

    public function create(BoxCreateRequest $boxCreateRequest): Box
    {
        $box = new Box();
        $box->goldenId = $boxCreateRequest->goldenId;
        $box->brand = $boxCreateRequest->brand;
        $box->country = $boxCreateRequest->country;
        $box->status = $boxCreateRequest->status;

        $this->repository->save($box);

        return $box;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function get(string $uuid): Box
    {
        return $this->repository->findOne($uuid);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function delete(string $uuid): void
    {
        $box = $this->get($uuid);
        $this->repository->delete($box);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function update(string $uuid, BoxUpdateRequest $boxUpdateRequest): Box
    {
        $box = $this->get($uuid);

        $box->goldenId = $boxUpdateRequest->goldenId;
        $box->brand = $boxUpdateRequest->brand;
        $box->country = $boxUpdateRequest->country;
        $box->status = $boxUpdateRequest->status;

        $this->repository->save($box);

        return $box;
    }

    public function replace(ProductRequest $productRequest): void
    {
        try {
            $box = $this->repository->findOneByGoldenId($productRequest->id);
        } catch (BoxNotFoundException $exception) {
            $box = new Box();
        }

        if (!empty($box->externalUpdatedAt) && $box->externalUpdatedAt > $productRequest->updatedAt) {
            throw new OutdatedBoxException();
        }

        $boxStatus = '';
        if (!empty($box->status)) {
            $boxStatus = $box->status;
        }
        $box->goldenId = $productRequest->id;
        $box->brand = $productRequest->sellableBrand ? $productRequest->sellableBrand->code : null;
        $box->country = $productRequest->sellableCountry ? $productRequest->sellableCountry->code : null;
        $box->status = $productRequest->status;
        $box->currency = $productRequest->listPrice ? $productRequest->listPrice->currencyCode : null;
        $box->externalUpdatedAt = $productRequest->updatedAt;

        $this->repository->save($box);
        $this->manageableProductService->dispatchForProduct($productRequest, $boxStatus);
    }

    /**
     * @throws BoxNotFoundException
     */
    public function insertPriceInfo(PriceInformationRequest $priceInformationRequest): void
    {
        $box = $this->repository->findOneByGoldenId($priceInformationRequest->product->id);
        $box->currency = $priceInformationRequest->averageValue ? $priceInformationRequest->averageValue->currencyCode : null;
        $this->repository->save($box);
    }
}
