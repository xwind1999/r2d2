<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Contract\Request\Internal\Experience\ExperienceCreateRequest;
use App\Contract\Request\Internal\Experience\ExperienceUpdateRequest;
use App\Contract\Response\Internal\Experience\ExperienceCreateResponse;
use App\Contract\Response\Internal\Experience\ExperienceGetResponse;
use App\Contract\Response\Internal\Experience\ExperienceUpdateResponse;
use App\Exception\Http\ResourceConflictException;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\ExperienceManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExperienceController
{
    /**
     * @Route("/internal/experience", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="experience")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=ExperienceCreateRequest::class)
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Experience created",
     *     @Model(type=ExperienceCreateResponse::class)
     * )
     */
    public function create(
        ExperienceCreateRequest $experienceCreateRequest,
        ExperienceManager $experienceManager
    ): ExperienceCreateResponse {
        try {
            $experience = $experienceManager->create($experienceCreateRequest);
        } catch (UniqueConstraintViolationException $exception) {
            throw ResourceConflictException::forContext([], $exception);
        }

        return new ExperienceCreateResponse($experience);
    }

    /**
     * @Route("/internal/experience/{uuid}", methods={"GET"}, format="json")
     *
     * @SWG\Tag(name="experience")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Experience successfully retrieved",
     *     @Model(type=ExperienceGetResponse::class)
     * )
     *
     * @throws UnprocessableEntityException
     * @throws ResourceNotFoundException
     */
    public function get(UuidInterface $uuid, ExperienceManager $experienceManager): ExperienceGetResponse
    {
        try {
            $experience = $experienceManager->get($uuid->toString());
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new ExperienceGetResponse($experience);
    }

    /**
     * @Route("/internal/experience/{uuid}", methods={"DELETE"}, format="json")
     *
     * @SWG\Tag(name="experience")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Experience deleted"
     * )
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function delete(UuidInterface $uuid, ExperienceManager $experienceManager): Response
    {
        try {
            $experienceManager->delete($uuid->toString());
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }

    /**
     * @Route("/internal/experience/{uuid}", methods={"PUT"}, format="json")
     *
     * @SWG\Tag(name="experience")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=ExperienceUpdateRequest::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Experience updated",
     *     @Model(type=ExperienceUpdateResponse::class)
     * )
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function put(
        UuidInterface $uuid,
        ExperienceUpdateRequest $experienceUpdateRequest,
        ExperienceManager $experienceManager
    ): ExperienceUpdateResponse {
        try {
            $experience = $experienceManager->update($uuid->toString(), $experienceUpdateRequest);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new ExperienceUpdateResponse($experience);
    }
}
