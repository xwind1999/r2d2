<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Contract\Request\Room\RoomCreateRequest;
use App\Contract\Request\Room\RoomUpdateRequest;
use App\Contract\Response\Room\RoomCreateResponse;
use App\Contract\Response\Room\RoomGetResponse;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\RoomManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Ramsey\Uuid\Uuid;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RoomController
{
    /**
     * @Route("/api/room", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="room")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=RoomCreateRequest::class)
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Room created",
     *     @Model(type=RoomCreateResponse::class)
     * )
     */
    public function create(RoomCreateRequest $roomCreateRequest, RoomManager $roomManager): RoomCreateResponse
    {
        $room = $roomManager->create($roomCreateRequest);

        return new RoomCreateResponse($room);
    }

    /**
     * @Route("/api/room/{uuid}", methods={"GET"}, format="json")
     *
     * @SWG\Tag(name="room")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Room successfully retrieved",
     *     @Model(type=RoomGetResponse::class)
     * )
     *
     * @throws UnprocessableEntityException
     * @throws ResourceNotFoundException
     */
    public function get(string $uuid, RoomManager $roomManager): RoomGetResponse
    {
        if (!Uuid::isValid($uuid)) {
            throw new UnprocessableEntityException();
        }

        try {
            $room = $roomManager->get($uuid);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new RoomGetResponse($room);
    }

    /**
     * @Route("/api/room/{uuid}", methods={"DELETE"}, format="json")
     *
     * @SWG\Tag(name="room")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Room deleted"
     * )
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function delete(string $uuid, RoomManager $roomManager): Response
    {
        if (!Uuid::isValid($uuid)) {
            throw new UnprocessableEntityException();
        }

        try {
            $roomManager->delete($uuid);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }

    /**
     * @Route("/api/room/{uuid}", methods={"PUT"}, format="json")
     *
     * @SWG\Tag(name="room")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=RoomUpdateRequest::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Room upated"
     * )
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function put(string $uuid, RoomUpdateRequest $roomUpdateRequest, RoomManager $roomManager): Response
    {
        if (!Uuid::isValid($uuid)) {
            throw new UnprocessableEntityException();
        }

        try {
            $roomManager->update($uuid, $roomUpdateRequest);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }
}
