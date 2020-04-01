<?php

declare(strict_types=1);

namespace App\Tests\Handler;

use App\Contract\Request\BroadcastListener\PartnerRequest;
use App\Handler\PartnerBroadcastHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\Handler\PartnerBroadcastHandler
 */
class PartnerBroadcastHandlerTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testHandlerMessage(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $partnerRequest = new PartnerRequest();
        $partnerRequest->uuid = $uuid;
        $partnerRequest->goldenId = '1234';
        $partnerRequest->status = 'alive';
        $partnerRequest->currency = 'USD';
        $partnerRequest->ceaseDate = new \DateTime('2020-10-10');

        $logger = $this->createMock(LoggerInterface::class);
        $partnerBroadcastHandler = new PartnerBroadcastHandler($logger);
        $this->assertEquals(null, $partnerBroadcastHandler->__invoke($partnerRequest));
    }
}
