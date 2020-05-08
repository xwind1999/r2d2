<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Contract\Request\Internal\Booking\BookingCreateRequest;
use App\Contract\Request\Internal\Booking\BookingUpdateRequest;
use App\Contract\Response\Internal\Booking\BookingCreateResponse;
use App\Contract\Response\Internal\Booking\BookingGetResponse;
use App\Contract\Response\Internal\Booking\BookingUpdateResponse;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\BookingManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookingController
{
    /**
     * @Route("/internal/booking", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="booking")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=BookingCreateRequest::class)
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Booking  created",
     *     @Model(type=BookingCreateResponse::class)
     * )l
     */
    public function create(BookingCreateRequest $bookingCreateRequest, BookingManager $bookingManager): BookingCreateResponse
    {
        $booking = $bookingManager->create($bookingCreateRequest);

        return new BookingCreateResponse($booking);
    }

    /**
     * @Route("/internal/booking/{uuid}", methods={"GET"}, format="json")
     *
     * @SWG\Tag(name="booking")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Booking  successfully retrieved",
     *     @Model(type=BookingGetResponse::class)
     * )
     *
     * @throws UnprocessableEntityException
     * @throws ResourceNotFoundException
     */
    public function get(UuidInterface $uuid, BookingManager $bookingManager): BookingGetResponse
    {
        try {
            $booking = $bookingManager->get($uuid->toString());
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new BookingGetResponse($booking);
    }

    /**
     * @Route("/internal/booking/{uuid}", methods={"DELETE"}, format="json")
     *
     * @SWG\Tag(name="booking")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Booking  deleted"
     * )
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function delete(UuidInterface $uuid, BookingManager $bookingManager): Response
    {
        try {
            $bookingManager->delete($uuid->toString());
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }

    /**
     * @Route("/internal/booking/{uuid}", methods={"PUT"}, format="json")
     *
     * @SWG\Tag(name="booking")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=BookingUpdateRequest::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Booking Date upated",
     *     @Model(type=BookingUpdateResponse::class)
     * )
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function put(UuidInterface $uuid, BookingUpdateRequest $bookingUpdateRequest, BookingManager $bookingManager): BookingUpdateResponse
    {
        try {
            $booking = $bookingManager->update($uuid->toString(), $bookingUpdateRequest);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new BookingUpdateResponse($booking);
    }
}
