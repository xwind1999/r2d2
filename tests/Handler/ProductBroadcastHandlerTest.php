<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Handler\ProductBroadcastHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\Handler\ProductBroadcastHandler
 */
class ProductBroadcastHandlerTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testHandlerMessage(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $productRequest = new ProductRequest();
        $productRequest->uuid = $uuid;
        $productRequest->goldenId = '1234';
        $productRequest->status = 'alive';
        $productRequest->currency = 'USD';
        $productRequest->ceaseDate = new \DateTime('2020-10-10');

        $logger = $this->createMock(LoggerInterface::class);
        $productBroadcastHandler = new ProductBroadcastHandler($logger);
        $this->assertEquals(null, $productBroadcastHandler->__invoke($productRequest));
    }
}
