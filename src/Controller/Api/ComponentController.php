<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Contract\Request\Component\ComponentCreateRequest;
use App\Contract\Request\Component\ComponentUpdateRequest;
use App\Contract\Response\Component\ComponentCreateResponse;
use App\Contract\Response\Component\ComponentGetResponse;
use App\Contract\Response\Component\ComponentUpdateResponse;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\ComponentManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ComponentController
{
    /**
     * @Route("/internal/component", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="component")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=ComponentCreateRequest::class)
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Component created",
     *     @Model(type=ComponentCreateResponse::class)
     * )
     */
    public function create(ComponentCreateRequest $componentCreateRequest, ComponentManager $componentManager): ComponentCreateResponse
    {
        $component = $componentManager->create($componentCreateRequest);

        return new ComponentCreateResponse($component);
    }

    /**
     * @Route("/internal/component/{uuid}", methods={"GET"}, format="json")
     *
     * @SWG\Tag(name="component")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Component successfully retrieved",
     *     @Model(type=ComponentGetResponse::class)
     * )
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
     * @SWG\Tag(name="component")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Component deleted"
     * )
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
     * @SWG\Tag(name="component")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=ComponentUpdateRequest::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Component updated",
     *     @Model(type=ComponentUpdateResponse::class)
     * )
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
