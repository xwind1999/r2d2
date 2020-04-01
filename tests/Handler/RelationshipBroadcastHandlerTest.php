<?php

declare(strict_types=1);

namespace App\Tests\Handler;

use App\Contract\Request\BroadcastListener\RelationshipRequest;
use App\Handler\RelationshipBroadcastHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\Handler\RelationshipBroadcastHandler
 */
class RelationshipBroadcastHandlerTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testHandlerMessage(): void
    {
        $relationshipRequest = new RelationshipRequest();
        $relationshipRequest->parentProduct = 'BB0000335658';
        $relationshipRequest->childProduct = 'HG0000335654';
        $relationshipRequest->sortOrder = 1;
        $relationshipRequest->isEnabled = true;
        $relationshipRequest->relationshipType = 'Box-Experience';
        $relationshipRequest->printType = 'Digital';

        $logger = $this->createMock(LoggerInterface::class);
        $relationshipBroadcastHandler = new RelationshipBroadcastHandler($logger);
        $this->assertEquals(null, $relationshipBroadcastHandler->__invoke($relationshipRequest));
    }
}
