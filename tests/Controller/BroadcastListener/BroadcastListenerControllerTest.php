<?php

declare(strict_types=1);

namespace App\Tests\Controller\BroadcastListener;

use App\Contract\Request\BroadcastListener\PartnerRequest;
use App\Contract\Request\BroadcastListener\PriceInformation\Price;
use App\Contract\Request\BroadcastListener\PriceInformationRequest;
use App\Contract\Request\BroadcastListener\Product\Partner;
use App\Contract\Request\BroadcastListener\Product\Product;
use App\Contract\Request\BroadcastListener\Product\Universe;
use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\BroadcastListener\RoomAvailabilityRequest;
use App\Contract\Request\BroadcastListener\RoomAvailabilityRequestList;
use App\Controller\BroadcastListener\BroadcastListenerController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \App\Controller\BroadcastListener\BroadcastListenerController
 * @group broadcast-listener
 */
class BroadcastListenerControllerTest extends TestCase
{
    /**
     * @var MessageBusInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $messageBus;

    private Envelope $envelope;

    public function setUp(): void
    {
        $this->envelope = new Envelope(new \stdClass());
        $this->messageBus = $this->createMock(MessageBusInterface::class);
    }

    /**
     * @covers ::productListener
     * @covers ::getBroadcastDateTimeFromRequest
     */
    public function testHandleProductsSuccessfully()
    {
        $universe = Universe::create('product universe');
        $partner = Partner::create('123456');
        $productRequest = new ProductRequest();
        $productRequest->id = '123456';
        $productRequest->name = 'product name';
        $productRequest->description = 'product description';
        $productRequest->universe = $universe;
        $productRequest->isReservable = true;
        $productRequest->isSellable = true;
        $productRequest->partner = $partner;

        $this->messageBus->expects($this->once())
            ->method('dispatch')
            ->willReturn($this->envelope);

        $request = new Request();
        $request->headers = new HeaderBag();

        $controller = new BroadcastListenerController();
        $response = $controller->productListener($request, $productRequest, $this->messageBus);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(202, $response->getStatusCode());
    }

    /**
     * @covers ::partnerListener
     */
    public function testHandlePartnersSuccessfully()
    {
        $partnerRequest = new PartnerRequest();
        $partnerRequest->uuid = '3fa85f64-5717-4562-b3fc-2c963f66afa6';
        $partnerRequest->id = '123456';
        $partnerRequest->currencyCode = 'USD';
        $partnerRequest->status = 'alive';
        $partnerRequest->partnerCeaseDate = new \DateTime();

        $this->messageBus->expects($this->once())
            ->method('dispatch')
            ->willReturn($this->envelope);

        $request = new Request();
        $request->headers = new HeaderBag();

        $controller = new BroadcastListenerController();
        $response = $controller->partnerListener($request, $partnerRequest, $this->messageBus);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(202, $response->getStatusCode());
    }

    /**
     * @covers ::relationshipListener
     */
    public function testHandleRelationshipsSuccessfully()
    {
        $relationshipRequest = new ProductRelationshipRequest();
        $relationshipRequest->parentProduct = 'BB0000335658';
        $relationshipRequest->childProduct = 'HG0000335654';
        $relationshipRequest->isEnabled = true;
        $relationshipRequest->relationshipType = 'Box-Experience';

        $this->messageBus->expects($this->once())
            ->method('dispatch')
            ->willReturn($this->envelope);

        $request = new Request();
        $request->headers = new HeaderBag();

        $controller = new BroadcastListenerController();
        $response = $controller->relationshipListener($request, $relationshipRequest, $this->messageBus);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(202, $response->getStatusCode());
    }

    /**
     * @covers ::priceInformationListener
     */
    public function testHandlePriceInformationSuccessfully()
    {
        $productDTO = new Product();
        $productDTO->id = '1264';
        $priceDTO = new Price();
        $priceDTO->amount = 12;
        $priceInformationRequest = new PriceInformationRequest();
        $priceInformationRequest->product = $productDTO;
        $priceInformationRequest->averageValue = $priceDTO;
        $priceInformationRequest->averageCommission = '5556';
        $priceInformationRequest->averageCommissionType = 'percentage';

        $this->messageBus->expects($this->once())
            ->method('dispatch')
            ->willReturn($this->envelope);

        $request = new Request();
        $request->headers = new HeaderBag();

        $controller = new BroadcastListenerController();
        $response = $controller->priceInformationListener($request, $priceInformationRequest, $this->messageBus);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(202, $response->getStatusCode());
    }

    /**
     * @covers ::roomAvailabilityListener
     */
    public function testHandleRoomAvailabilitySuccessfully()
    {
        $product = new Product();
        $product->id = '299994';
        $roomAvailabilityRequestList = new RoomAvailabilityRequestList();
        $roomAvailabilityRequest = new RoomAvailabilityRequest();

        $roomAvailabilityRequest->product = $product;
        $roomAvailabilityRequest->quantity = 2;
        $roomAvailabilityRequest->dateFrom = new \DateTime('+5 days');
        $roomAvailabilityRequest->dateTo = new \DateTime('+8 days');
        $roomAvailabilityRequest->updatedAt = new \DateTime('now');

        $roomAvailabilityRequest2 = (clone $roomAvailabilityRequest);
        $roomAvailabilityRequest2->product->id = '218439';
        $roomAvailabilityRequest2->quantity = 5;

        $roomAvailabilityRequest3 = (clone $roomAvailabilityRequest);
        $roomAvailabilityRequest3->product->id = '315172';
        $roomAvailabilityRequest3->quantity = 1;

        $roomAvailabilityRequestList->items = [
            $roomAvailabilityRequest,
            $roomAvailabilityRequest2,
            $roomAvailabilityRequest3,
        ];

        $this->messageBus->expects($this->atLeast(3))
            ->method('dispatch')
            ->willReturn($this->envelope);

        $controller = new BroadcastListenerController();
        $response = $controller->roomAvailabilityListener($roomAvailabilityRequestList, $this->messageBus);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(202, $response->getStatusCode());
    }
}
