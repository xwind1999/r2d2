<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\Partner\PartnerCreateRequest;
use App\Contract\Request\Partner\PartnerUpdateRequest;
use App\Entity\Partner;
use App\Exception\Repository\EntityNotFoundException;
use App\Repository\PartnerRepository;
use Doctrine\ORM\EntityManagerInterface;

class PartnerManager
{
    protected EntityManagerInterface $em;

    protected PartnerRepository $repository;

    public function __construct(EntityManagerInterface $em, PartnerRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    public function create(PartnerCreateRequest $partnerCreateRequest): Partner
    {
        $partner = new Partner();
        $partner->goldenId = $partnerCreateRequest->goldenId;
        $partner->status = $partnerCreateRequest->status;
        $partner->currency = $partnerCreateRequest->currency;
        $partner->ceaseDate = $partnerCreateRequest->ceaseDate;

        $this->em->persist($partner);
        $this->em->flush();

        return $partner;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function get(string $uuid): Partner
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
    public function update(PartnerUpdateRequest $partnerUpdateRequest): void
    {
        $partner = $this->get($partnerUpdateRequest->uuid);

        $partner->goldenId = $partnerUpdateRequest->goldenId;
        $partner->status = $partnerUpdateRequest->status;
        $partner->currency = $partnerUpdateRequest->currency;
        $partner->ceaseDate = $partnerUpdateRequest->ceaseDate;

        $this->em->persist($partner);
        $this->em->flush();
    }
}
