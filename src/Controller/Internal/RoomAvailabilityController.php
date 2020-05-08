<?php

declare(strict_types=1);

namespace App\Controller\Internal;

use App\Contract\Request\Internal\RoomAvailability\RoomAvailabilityCreateRequest;
use App\Contract\Request\Internal\RoomAvailability\RoomAvailabilityUpdateRequest;
use App\Contract\Response\Internal\RoomAvailability\RoomAvailabilityCreateResponse;
use App\Contract\Response\Internal\RoomAvailability\RoomAvailabilityGetResponse;
use App\Contract\Response\Internal\RoomAvailability\RoomAvailabilityUpdateResponse;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\RoomAvailabilityManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RoomAvailabilityController
{
    /**
     * @Route("/internal/room-availability", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="room-availability")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=RoomAvailabilityCreateRequest::class)
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Room Availability created",
     *     @Model(type=RoomAvailabilityCreateResponse::class)
     * )
     */
    public function create(RoomAvailabilityCreateRequest $roomAvailabilityCreateRequest, RoomAvailabilityManager $roomAvailabilityManager): RoomAvailabilityCreateResponse
    {
        $roomAvailability = $roomAvailabilityManager->create($roomAvailabilityCreateRequest);

        return new RoomAvailabilityCreateResponse($roomAvailability);
    }

    /**
     * @Route("/internal/room-availability/{uuid}", methods={"GET"}, format="json")
     *
     * @SWG\Tag(name="room-availability")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Room Availability successfully retrieved",
     *     @Model(type=RoomAvailabilityGetResponse::class)
     * )
     *
     * @throws UnprocessableEntityException
     * @throws ResourceNotFoundException
     */
    public function get(UuidInterface $uuid, RoomAvailabilityManager $roomAvailabilityManager): RoomAvailabilityGetResponse
    {
        try {
            $roomAvailability = $roomAvailabilityManager->get($uuid->toString());
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new RoomAvailabilityGetResponse($roomAvailability);
    }

    /**
     * @Route("/internal/room-availability/{uuid}", methods={"DELETE"}, format="json")
     *
     * @SWG\Tag(name="room-availability")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Room Availability deleted"
     * )
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function delete(UuidInterface $uuid, RoomAvailabilityManager $roomAvailabilityManager): Response
    {
        try {
            $roomAvailabilityManager->delete($uuid->toString());
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }

    /**
     * @Route("/internal/room-availability/{uuid}", methods={"PUT"}, format="json")
     *
     * @SWG\Tag(name="room-availability")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=RoomAvailabilityUpdateRequest::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Room Availability updated",
     *     @Model(type=RoomAvailabilityUpdateResponse::class)
     * )
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function put(UuidInterface $uuid, RoomAvailabilityUpdateRequest $roomAvailabilityUpdateRequest, RoomAvailabilityManager $roomAvailabilityManager): RoomAvailabilityUpdateResponse
    {
        try {
            $roomAvailability = $roomAvailabilityManager->update($uuid->toString(), $roomAvailabilityUpdateRequest);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new RoomAvailabilityUpdateResponse($roomAvailability);
    }
}
