<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\RateBand\RateBandCreateRequest;
use App\Contract\Request\RateBand\RateBandUpdateRequest;
use App\Entity\RateBand;
use App\Exception\Repository\EntityNotFoundException;
use App\Repository\RateBandRepository;
use Doctrine\ORM\EntityManagerInterface;

class RateBandManager
{
    protected EntityManagerInterface $em;

    protected RateBandRepository $repository;

    public function __construct(EntityManagerInterface $em, RateBandRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    public function create(RateBandCreateRequest $rateBandCreateRequest): RateBand
    {
        $rateBand = new RateBand();
        $rateBand->goldenId = $rateBandCreateRequest->goldenId;
        $rateBand->partnerGoldenId = $rateBandCreateRequest->partnerGoldenId;
        $rateBand->name = $rateBandCreateRequest->name;

        $this->em->persist($rateBand);
        $this->em->flush();

        return $rateBand;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function get(string $uuid): RateBand
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
    public function update(string $uuid, RateBandUpdateRequest $rateBandUpdateRequest): void
    {
        $rateBand = $this->get($uuid);

        $rateBand->goldenId = $rateBandUpdateRequest->goldenId;
        $rateBand->partnerGoldenId = $rateBandUpdateRequest->partnerGoldenId;
        $rateBand->name = $rateBandUpdateRequest->name;

        $this->em->persist($rateBand);
        $this->em->flush();
    }
}
