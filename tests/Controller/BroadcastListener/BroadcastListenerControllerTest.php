<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Contract\Request\BroadcastListener\PartnerRequest;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Controller\BroadcastListener\BroadcastListenerController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @coversDefaultClass \App\Controller\BroadcastListener\BroadcastListenerController
 */
class BroadcastListenerControllerTest extends TestCase
{
    /**
     * @covers ::handleProducts
     */
    public function testHandleProducts()
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

        $controller = new BroadcastListenerController();
        $response = $controller->productListener($productRequest);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(202, $response->getStatusCode());
    }

    /**
     * @covers ::handlePartners
     */
    public function testHandlePartners()
    {
        $partnerRequest = new PartnerRequest();
        $partnerRequest->uuid = '3fa85f64-5717-4562-b3fc-2c963f66afa6';
        $partnerRequest->goldenId = '123456';
        $partnerRequest->currency = 'USD';
        $partnerRequest->status = 'alive';
        $partnerRequest->ceaseDate = new \DateTime();

        $controller = new BroadcastListenerController();
        $response = $controller->partnerListener($partnerRequest);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(202, $response->getStatusCode());
    }
}
