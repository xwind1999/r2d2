<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\Box\BoxCreateRequest;
use App\Contract\Request\Box\BoxUpdateRequest;
use App\Entity\Box;
use App\Exception\Repository\EntityNotFoundException;
use App\Repository\BoxRepository;
use Doctrine\ORM\EntityManagerInterface;

class BoxManager
{
    protected EntityManagerInterface $em;

    protected BoxRepository $repository;

    public function __construct(EntityManagerInterface $em, BoxRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    public function create(BoxCreateRequest $boxCreateRequest): Box
    {
        $box = new Box();
        $box->goldenId = $boxCreateRequest->goldenId;
        $box->brand = $boxCreateRequest->brand;
        $box->country = $boxCreateRequest->country;
        $box->status = $boxCreateRequest->status;

        $this->em->persist($box);
        $this->em->flush();

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
        $this->em->remove($box);
        $this->em->flush();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function update(BoxUpdateRequest $boxUpdateRequest): void
    {
        $box = $this->get($boxUpdateRequest->uuid);

        $box->goldenId = $boxUpdateRequest->goldenId;
        $box->brand = $boxUpdateRequest->brand;
        $box->country = $boxUpdateRequest->country;
        $box->status = $boxUpdateRequest->status;

        $this->em->persist($box);
        $this->em->flush();
    }
}
