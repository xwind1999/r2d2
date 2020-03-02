<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Contract\Request\Experience\ExperienceCreateRequest;
use App\Contract\Request\Experience\ExperienceUpdateRequest;
use App\Contract\Response\Experience\ExperienceCreateResponse;
use App\Contract\Response\Experience\ExperienceGetResponse;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\ExperienceManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Ramsey\Uuid\Uuid;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExperienceController
{
    /**
     * @Route("/api/experience", methods={"POST"}, format="json")
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
    public function create(ExperienceCreateRequest $experienceCreateRequest, ExperienceManager $experienceManager): ExperienceCreateResponse
    {
        $experience = $experienceManager->create($experienceCreateRequest);

        return new ExperienceCreateResponse($experience);
    }

    /**
     * @Route("/api/experience/{uuid}", methods={"GET"}, format="json")
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
    public function get(string $uuid, ExperienceManager $experienceManager): ExperienceGetResponse
    {
        if (!Uuid::isValid($uuid)) {
            throw new UnprocessableEntityException();
        }

        try {
            $experience = $experienceManager->get($uuid);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new ExperienceGetResponse($experience);
    }

    /**
     * @Route("/api/experience/{uuid}", methods={"DELETE"}, format="json")
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
    public function delete(string $uuid, ExperienceManager $experienceManager): Response
    {
        if (!Uuid::isValid($uuid)) {
            throw new UnprocessableEntityException();
        }

        try {
            $experienceManager->delete($uuid);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }

    /**
     * @Route("/api/experience", methods={"PUT"}, format="json")
     *
     * @SWG\Tag(name="experience")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=ExperienceUpdateRequest::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Experience upated"
     * )
     *
     * @throws ResourceNotFoundException
     */
    public function put(ExperienceUpdateRequest $experienceUpdateRequest, ExperienceManager $experienceManager): Response
    {
        try {
            $experienceManager->update($experienceUpdateRequest);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }
}
