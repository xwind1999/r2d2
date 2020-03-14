<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Contract\Request\Room\RoomCreateRequest;
use App\Contract\Request\Room\RoomUpdateRequest;
use App\Contract\Response\Room\RoomCreateResponse;
use App\Contract\Response\Room\RoomGetResponse;
use App\Contract\Response\Room\RoomUpdateResponse;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\RoomManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
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
    public function get(UuidInterface $uuid, RoomManager $roomManager): RoomGetResponse
    {
        try {
            $room = $roomManager->get($uuid->toString());
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
    public function delete(UuidInterface $uuid, RoomManager $roomManager): Response
    {
        try {
            $roomManager->delete($uuid->toString());
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
     *     response=200,
     *     description="Room updated",
     *     @Model(type=RoomUpdateResponse::class)
     * )
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function put(UuidInterface $uuid, RoomUpdateRequest $roomUpdateRequest, RoomManager $roomManager): RoomUpdateResponse
    {
        try {
            $room = $roomManager->update($uuid->toString(), $roomUpdateRequest);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new RoomUpdateResponse($room);
    }
}
