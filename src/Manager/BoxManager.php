<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Internal\Box\BoxCreateRequest;
use App\Contract\Request\Internal\Box\BoxUpdateRequest;
use App\Entity\Box;
use App\Exception\Repository\BoxNotFoundException;
use App\Exception\Repository\EntityNotFoundException;
use App\Repository\BoxRepository;

class BoxManager
{
    protected BoxRepository $repository;

    public function __construct(BoxRepository $repository)
    {
        $this->repository = $repository;
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

        $box->goldenId = $productRequest->id;
        $box->brand = $productRequest->sellableBrand ? $productRequest->sellableBrand->code : null;
        $box->country = $productRequest->sellableCountry ? $productRequest->sellableCountry->code : null;
        $box->status = $productRequest->status;

        $this->repository->save($box);
    }
}
