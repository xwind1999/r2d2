<?php

declare(strict_types=1);

namespace App\Controller\Internal;

use App\Contract\Request\Internal\Component\ComponentCreateRequest;
use App\Contract\Request\Internal\Component\ComponentUpdateRequest;
use App\Contract\Response\Internal\Component\ComponentCreateResponse;
use App\Contract\Response\Internal\Component\ComponentGetResponse;
use App\Contract\Response\Internal\Component\ComponentUpdateResponse;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\ComponentManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ComponentController
{
    /**
     * @Route("/internal/component", methods={"POST"}, format="json")
     *
     * @OA\Tag(name="component")
     * @OA\Parameter(
     *         name="body",
     *         in="query",
     *         @Model(type=ComponentCreateRequest::class)
     * )
     * @OA\Response(
     *     response=201,
     *     description="Component created",
     *     @Model(type=ComponentCreateResponse::class)
     * )
     * @Security(name="basic")
     */
    public function create(ComponentCreateRequest $componentCreateRequest, ComponentManager $componentManager): ComponentCreateResponse
    {
        $component = $componentManager->create($componentCreateRequest);

        return new ComponentCreateResponse($component);
    }

    /**
     * @Route("/internal/component/{uuid}", methods={"GET"}, format="json")
     *
     * @OA\Tag(name="component")
     * @OA\Parameter(
     *     name="uuid",
     *     in="path",
     *     @OA\Schema(
     *         type="string",
     *         format="uuid"
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Component successfully retrieved",
     *     @Model(type=ComponentGetResponse::class)
     * )
     * @Security(name="basic")
     *
     * @throws UnprocessableEntityException
     * @throws ResourceNotFoundException
     */
    public function get(UuidInterface $uuid, ComponentManager $componentManager): ComponentGetResponse
    {
        try {
            $component = $componentManager->get($uuid->toString());
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new ComponentGetResponse($component);
    }

    /**
     * @Route("/internal/component/{uuid}", methods={"DELETE"}, format="json")
     *
     * @OA\Tag(name="component")
     * @OA\Parameter(
     *     name="uuid",
     *     in="path",
     *     @OA\Schema(
     *         type="string",
     *         format="uuid"
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Component deleted"
     * )
     * @Security(name="basic")
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function delete(UuidInterface $uuid, ComponentManager $componentManager): Response
    {
        try {
            $componentManager->delete($uuid->toString());
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }

    /**
     * @Route("/internal/component/{uuid}", methods={"PUT"}, format="json")
     *
     * @OA\Tag(name="component")
     * @OA\Parameter(
     *     name="uuid",
     *     in="path",
     *     @OA\Schema(
     *         type="string",
     *         format="uuid"
     *     )
     * )
     * @OA\Parameter(
     *         name="body",
     *         in="query",
     *         @Model(type=ComponentUpdateRequest::class)
     * )
     * @OA\Response(
     *     response=200,
     *     description="Component updated",
     *     @Model(type=ComponentUpdateResponse::class)
     * )
     * @Security(name="basic")
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function put(UuidInterface $uuid, ComponentUpdateRequest $componentUpdateRequest, ComponentManager $componentManager): ComponentUpdateResponse
    {
        try {
            $component = $componentManager->update($uuid->toString(), $componentUpdateRequest);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new ComponentUpdateResponse($component);
    }
}
