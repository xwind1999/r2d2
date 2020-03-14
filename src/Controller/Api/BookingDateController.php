<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Contract\Request\BookingDate\BookingDateCreateRequest;
use App\Contract\Request\BookingDate\BookingDateUpdateRequest;
use App\Contract\Response\BookingDate\BookingDateCreateResponse;
use App\Contract\Response\BookingDate\BookingDateGetResponse;
use App\Contract\Response\BookingDate\BookingDateUpdateResponse;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\BookingDateManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookingDateController
{
    /**
     * @Route("/api/booking-date", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="booking-date")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=BookingDateCreateRequest::class)
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Booking Date created",
     *     @Model(type=BookingDateCreateResponse::class)
     * )l
     */
    public function create(BookingDateCreateRequest $bookingDateCreateRequest, BookingDateManager $bookingDateManager): BookingDateCreateResponse
    {
        $bookingDate = $bookingDateManager->create($bookingDateCreateRequest);

        return new BookingDateCreateResponse($bookingDate);
    }

    /**
     * @Route("/api/booking-date/{uuid}", methods={"GET"}, format="json")
     *
     * @SWG\Tag(name="booking-date")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Booking Date successfully retrieved",
     *     @Model(type=BookingDateGetResponse::class)
     * )
     *
     * @throws UnprocessableEntityException
     * @throws ResourceNotFoundException
     */
    public function get(UuidInterface $uuid, BookingDateManager $bookingDateManager): BookingDateGetResponse
    {
        try {
            $bookingDate = $bookingDateManager->get($uuid->toString());
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new BookingDateGetResponse($bookingDate);
    }

    /**
     * @Route("/api/booking-date/{uuid}", methods={"DELETE"}, format="json")
     *
     * @SWG\Tag(name="booking-date")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Booking Date deleted"
     * )
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function delete(UuidInterface $uuid, BookingDateManager $bookingDateManager): Response
    {
        try {
            $bookingDateManager->delete($uuid->toString());
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }

    /**
     * @Route("/api/booking-date/{uuid}", methods={"PUT"}, format="json")
     *
     * @SWG\Tag(name="booking-date")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=BookingDateUpdateRequest::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Booking Date upated",
     *     @Model(type=BookingDateUpdateResponse::class)
     * )
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function put(UuidInterface $uuid, BookingDateUpdateRequest $bookingDateUpdateRequest, BookingDateManager $bookingDateManager): BookingDateUpdateResponse
    {
        try {
            $bookingDate = $bookingDateManager->update($uuid->toString(), $bookingDateUpdateRequest);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new BookingDateUpdateResponse($bookingDate);
    }
}
