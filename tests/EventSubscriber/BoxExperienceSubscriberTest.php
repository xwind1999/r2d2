<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Event\ProductRelationship\BoxExperienceRelationshipBroadcastEvent;
use App\EventSubscriber\BoxExperienceSubscriber;
use App\Exception\Repository\BoxNotFoundException;
use App\Exception\Repository\EntityNotFoundException;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Manager\BoxExperienceManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\EventSubscriber\BoxExperienceSubscriber
 */
class BoxExperienceSubscriberTest extends TestCase
{
    /**
     * @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $logger;

    /**
     * @var BoxExperienceManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $boxExperienceManager;

    /**
     * @var BoxExperienceRelationshipBroadcastEvent|\PHPUnit\Framework\MockObject\MockObject
     */
    private $boxExperienceEvent;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->boxExperienceManager = $this->createMock(BoxExperienceManager::class);
        $this->boxExperienceEvent = $this->createMock(BoxExperienceRelationshipBroadcastEvent::class);
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals(
            [BoxExperienceRelationshipBroadcastEvent::EVENT_NAME => ['handleMessage']],
            BoxExperienceSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     */
    public function testHandleMessage(): void
    {
        $this->boxExperienceManager->expects($this->once())->method('replace')->willReturnSelf();
        $boxExperienceSubscriber = new BoxExperienceSubscriber($this->logger, $this->boxExperienceManager);

        $this->assertEquals(null, $boxExperienceSubscriber->handleMessage($this->boxExperienceEvent));
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     * @covers \App\Exception\Repository\BoxNotFoundException
     * @covers \App\Exception\Repository\ExperienceNotFoundException
     * @covers \App\Contract\Request\BroadcastListener\ProductRelationshipRequest::getContext
     *
     * @dataProvider sampleException
     */
    public function testHandleMessageThrowsBoxNotFoundException(EntityNotFoundException $exception): void
    {
        $boxExperienceSubscriber = new BoxExperienceSubscriber($this->logger, $this->boxExperienceManager);

        $this->boxExperienceManager
            ->expects($this->once())
            ->method('replace')
            ->willThrowException($exception)
        ;
        $this->logger->expects($this->once())->method('warning')->willReturn(null);

        $relationshipRequest = new ProductRelationshipRequest();
        $relationshipRequest->childProduct = '111';
        $relationshipRequest->parentProduct = '222';
        $relationshipRequest->sortOrder = 1;
        $relationshipRequest->isEnabled = true;
        $relationshipRequest->relationshipType = 'BOX-EXPERIENCE';

        $this->boxExperienceEvent
            ->expects($this->exactly(2))
            ->method('getProductRelationshipRequest')
            ->willReturn($relationshipRequest)
        ;
        $boxExperienceSubscriber->handleMessage($this->boxExperienceEvent);
    }

    public function sampleException(): array
    {
        return [
            [new BoxNotFoundException()],
            [new ExperienceNotFoundException()],
        ];
    }
}
