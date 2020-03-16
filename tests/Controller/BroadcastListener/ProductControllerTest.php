<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Contract\Request\Product\ProductCreateRequest;
use App\Contract\Request\Product\ProductUpdateRequest;
use App\Contract\Response\Product\ProductCreateResponse;
use App\Controller\BroadcastListener\ProductController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @coversDefaultClass \App\Controller\BroadcastListener\ProductController
 */
class ProductControllerTest extends TestCase
{
    /**
     * @covers ::put
     */
    public function testPut()
    {
        $productUpdateRequest = new ProductUpdateRequest();
        $controller = new ProductController();
        $response = $controller->put($productUpdateRequest);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(202, $response->getStatusCode());
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\Product\ProductCreateResponse::__construct
     */
    public function testCreate()
    {
        $productCreateRequest = new ProductCreateRequest();
        $productCreateRequest->uuid = '3fa85f64-5717-4562-b3fc-2c963f66afa6';
        $productCreateRequest->goldenId = '123456';
        $productCreateRequest->name = 'product name';
        $productCreateRequest->description = 'product description';
        $productCreateRequest->universe = 'product universe';
        $productCreateRequest->isReservable = true;
        $productCreateRequest->isSellable = true;
        $productCreateRequest->partnerGoldenId = '123456';

        $controller = new ProductController();
        $response = $controller->create($productCreateRequest);
        $this->assertInstanceOf(ProductCreateResponse::class, $response);
        $this->assertEquals(201, $response->getHttpCode());
    }
}
