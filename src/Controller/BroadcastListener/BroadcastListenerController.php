<?php

declare(strict_types=1);

namespace App\Controller\BroadcastListener;

use App\Contract\Request\BroadcastListener\PartnerRequest;
use App\Contract\Request\BroadcastListener\PriceInformationRequest;
use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\BroadcastListener\RoomAvailabilityRequest;
use App\Contract\Request\BroadcastListener\RoomAvailabilityRequestList;
use App\Contract\Request\BroadcastListener\RoomPriceRequest;
use App\Contract\Request\BroadcastListener\RoomPriceRequestList;
use App\Manager\RoomAvailabilityManager;
use App\Manager\RoomPriceManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class BroadcastListenerController
{
    private const EAI_TIMESTAMP_DIVISOR = 1000;
    private const EAI_TIMESTAMP_HEADER = 'x-eai-timestamp';

    /**
     * @Route("/broadcast-listener/product", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="broadcast-listener")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=ProductRequest::class)
     * )
     * @SWG\Response(
     *     response=202,
     *     description="Product handled")
     * )
     * @Security(name="basic")
     */
    public function productListener(Request $request, ProductRequest $productRequest, MessageBusInterface $messageBus): Response
    {
        $productRequest->updatedAt = $this->getBroadcastDateTimeFromRequest($request);

        $messageBus->dispatch($productRequest);

        return new Response(null, 202);
    }

    /**
     * @Route("/broadcast-listener/partner", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="broadcast-listener")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=PartnerRequest::class)
     * )
     * @SWG\Response(
     *     response=202,
     *     description="Partner handled")
     * )
     * @Security(name="basic")
     */
    public function partnerListener(Request $request, PartnerRequest $partnerRequest, MessageBusInterface $messageBus): Response
    {
        $partnerRequest->updatedAt = $this->getBroadcastDateTimeFromRequest($request);

        $messageBus->dispatch($partnerRequest);

        return new Response(null, 202);
    }

    /**
     * @Route("/broadcast-listener/product-relationship", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="broadcast-listener")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=ProductRelationshipRequest::class)
     * )
     * @SWG\Response(
     *     response=202,
     *     description="Relationship handled")
     * )
     * @Security(name="basic")
     */
    public function relationshipListener(
        Request $request,
        ProductRelationshipRequest $relationshipRequest,
        MessageBusInterface $messageBus
    ): Response {
        $relationshipRequest->updatedAt = $this->getBroadcastDateTimeFromRequest($request);

        $messageBus->dispatch($relationshipRequest);

        return new Response(null, 202);
    }

    /**
     * @Route("/broadcast-listener/price-information", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="broadcast-listener")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=PriceInformationRequest::class)
     * )
     * @SWG\Response(
     *     response=202,
     *     description="Price information handled")
     * )
     * @Security(name="basic")
     */
    public function priceInformationListener(
        Request $request,
        PriceInformationRequest $priceInformationRequest,
        MessageBusInterface $messageBus
    ): Response {
        $priceInformationRequest->updatedAt = $this->getBroadcastDateTimeFromRequest($request);

        $messageBus->dispatch($priceInformationRequest);

        return new Response(null, 202);
    }

    /**
     * @Route("/broadcast-listener/room-availability", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="broadcast-listener")
     * @SWG\Parameter(
     *    name="body",
     *    in="body",
     *    @SWG\Schema(
     *        type="array",
     *        @SWG\Items(
     *            ref=@Model(type=RoomAvailabilityRequest::class)
     *        )
     *    )
     * )
     *
     * @SWG\Response(
     *     response=202,
     *     description="Room availability handled")
     * )
     * @Security(name="basic")
     */
    public function roomAvailabilityListener(
        RoomAvailabilityRequestList $roomAvailabilityRequestList,
        RoomAvailabilityManager $roomAvailabilityManager
    ): Response {
        $roomAvailabilityManager->dispatchRoomAvailabilitiesRequest($roomAvailabilityRequestList);

        return new Response(null, 202);
    }

    /**
     * @Route("/broadcast-listener/room-price", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="broadcast-listener")
     * @SWG\Parameter(
     *    name="body",
     *    in="body",
     *    @SWG\Schema(
     *        type="array",
     *        @SWG\Items(
     *            ref=@Model(type=RoomPriceRequest::class)
     *        )
     *    )
     * )
     * @SWG\Response(
     *     response=202,
     *     description="Room price handled")
     * )
     * @Security(name="basic")
     */
    public function roomPriceListener(
        RoomPriceRequestList $roomPriceRequestList,
        RoomPriceManager $roomPriceManager
    ): Response {
        $roomPriceManager->dispatchRoomPricesRequest($roomPriceRequestList);

        return new Response(null, 202);
    }

    private function getBroadcastDateTimeFromRequest(Request $request): ?\DateTime
    {
        $timestamp = (int) $request->headers->get(self::EAI_TIMESTAMP_HEADER, '0') / self::EAI_TIMESTAMP_DIVISOR;
        $dateTime = \DateTime::createFromFormat('U.u', (string) $timestamp);

        return $dateTime ?: null;
    }
}
