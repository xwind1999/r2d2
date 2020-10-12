<?php

declare(strict_types=1);

namespace App\Controller\Internal;

use App\Contract\Request\Internal\BoxExperience\BoxExperienceCreateRequest;
use App\Contract\Request\Internal\BoxExperience\BoxExperienceDeleteRequest;
use App\Contract\Response\Internal\BoxExperience\BoxExperienceCreateResponse;
use App\Exception\Http\ResourceConflictException;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Manager\BoxExperience\RelationshipAlreadyExistsException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\BoxExperienceManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BoxExperienceController
{
    /**
     * @Route("/internal/box-experience", methods={"POST"}, format="json")
     *
     * @OA\Tag(name="box-experience")
     * @OA\Parameter(
     *         name="body",
     *         in="query",
     *         @Model(type=BoxExperienceCreateRequest::class)
     * )
     * @OA\Response(
     *     response=201,
     *     description="Relationship created",
     *     @Model(type=BoxExperienceCreateResponse::class)
     * )
     * @OA\Response(
     *     response=409,
     *     description="Relationship already exists"
     * )
     * @OA\Response(
     *     response=404,
     *     description="Resource not found"
     * )
     * @Security(name="basic")
     */
    public function create(BoxExperienceCreateRequest $boxExperienceCreateRequest, BoxExperienceManager $boxExperienceManager): BoxExperienceCreateResponse
    {
        try {
            $boxExperience = $boxExperienceManager->create($boxExperienceCreateRequest);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        } catch (RelationshipAlreadyExistsException $exception) {
            throw new ResourceConflictException();
        }

        return new BoxExperienceCreateResponse($boxExperience);
    }

    /**
     * @Route("/internal/box-experience", methods={"DELETE"}, format="json")
     *
     * @OA\Tag(name="box-experience")
     * @OA\Parameter(
     *         name="body",
     *         in="query",
     *         @Model(type=BoxExperienceDeleteRequest::class)
     * )
     * @OA\Response(
     *     response=204,
     *     description="Relationship deleted"
     * )
     * @OA\Response(
     *     response=404,
     *     description="Resource not found"
     * )
     * @Security(name="basic")
     *
     * @throws ResourceNotFoundException
     */
    public function delete(BoxExperienceDeleteRequest $boxExperienceDeleteRequest, BoxExperienceManager $boxExperienceManager): Response
    {
        try {
            $boxExperienceManager->delete($boxExperienceDeleteRequest);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }
}
