<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Contract\Request\BroadcastListener\PartnerRequest;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\BroadcastListener\RelationshipRequest;
use App\Controller\BroadcastListener\BroadcastListenerController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \App\Controller\BroadcastListener\BroadcastListenerController
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
     */
    public function testHandleProductsSuccessfully()
    {
        $productRequest = new ProductRequest();
        $productRequest->uuid = '3fa85f64-5717-4562-b3fc-2c963f66afa6';
        $productRequest->goldenId = '123456';
        $productRequest->name = 'product name';
        $productRequest->description = 'product description';
        $productRequest->universe = 'product universe';
        $productRequest->isReservable = true;
        $productRequest->isSellable = true;
        $productRequest->partnerGoldenId = '123456';

        $this->messageBus->expects($this->once())
            ->method('dispatch')
            ->willReturn($this->envelope);

        $controller = new BroadcastListenerController();
        $response = $controller->productListener($productRequest, $this->messageBus);
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
        $partnerRequest->goldenId = '123456';
        $partnerRequest->currency = 'USD';
        $partnerRequest->status = 'alive';
        $partnerRequest->ceaseDate = new \DateTime();

        $this->messageBus->expects($this->once())
            ->method('dispatch')
            ->willReturn($this->envelope);

        $controller = new BroadcastListenerController();
        $response = $controller->partnerListener($partnerRequest, $this->messageBus);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(202, $response->getStatusCode());
    }

    /**
     * @covers ::relationshipListener
     */
    public function testHandleRelationshipsSuccessfully()
    {
        $relationshipRequest = new RelationshipRequest();
        $relationshipRequest->parentProduct = 'BB0000335658';
        $relationshipRequest->childProduct = 'HG0000335654';
        $relationshipRequest->sortOrder = 1;
        $relationshipRequest->isEnabled = true;
        $relationshipRequest->relationshipType = 'Box-Experience';
        $relationshipRequest->printType = 'Digital';
        $relationshipRequest->childCount = 4;
        $relationshipRequest->childQuantity = 0;

        $this->messageBus->expects($this->once())
            ->method('dispatch')
            ->willReturn($this->envelope);

        $controller = new BroadcastListenerController();
        $response = $controller->relationshipListener($relationshipRequest, $this->messageBus);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(202, $response->getStatusCode());
    }
}
