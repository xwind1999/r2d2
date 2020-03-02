<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\Experience\ExperienceCreateRequest;
use App\Contract\Request\Experience\ExperienceUpdateRequest;
use App\Entity\Experience;
use App\Exception\Repository\EntityNotFoundException;
use App\Repository\ExperienceRepository;
use Doctrine\ORM\EntityManagerInterface;

class ExperienceManager
{
    protected EntityManagerInterface $em;

    protected ExperienceRepository $repository;

    public function __construct(EntityManagerInterface $em, ExperienceRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    public function create(ExperienceCreateRequest $experienceCreateRequest): Experience
    {
        $experience = new Experience();

        $experience->goldenId = $experienceCreateRequest->goldenId;
        $experience->partnerGoldenId = $experienceCreateRequest->partnerGoldenId;
        $experience->name = $experienceCreateRequest->name;
        $experience->description = $experienceCreateRequest->description;
        $experience->duration = $experienceCreateRequest->duration;

        $this->em->persist($experience);
        $this->em->flush();

        return $experience;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function get(string $uuid): Experience
    {
        return $this->repository->findOne($uuid);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function delete(string $uuid): void
    {
        $experience = $this->get($uuid);
        $this->em->remove($experience);
        $this->em->flush();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function update(ExperienceUpdateRequest $experienceUpdateRequest): void
    {
        $experience = $this->get($experienceUpdateRequest->uuid);

        $experience->goldenId = $experienceUpdateRequest->goldenId;
        $experience->partnerGoldenId = $experienceUpdateRequest->partnerGoldenId;
        $experience->name = $experienceUpdateRequest->name;
        $experience->description = $experienceUpdateRequest->description;
        $experience->duration = $experienceUpdateRequest->duration;

        $this->em->persist($experience);
        $this->em->flush();
    }
}
