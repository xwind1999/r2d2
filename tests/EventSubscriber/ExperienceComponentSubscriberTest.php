<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Event\ProductRelationship\ExperienceComponentRelationshipBroadcastEvent;
use App\EventSubscriber\ExperienceComponentSubscriber;
use App\Exception\Repository\EntityNotFoundException;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Exception\Repository\RoomNotFoundException;
use App\Manager\ExperienceComponentManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\EventSubscriber\ExperienceComponentSubscriber
 */
class ExperienceComponentSubscriberTest extends TestCase
{
    /**
     * @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $logger;

    /**
     * @var ExperienceComponentManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $experienceComponentManager;

    /**
     * @var ExperienceComponentRelationshipBroadcastEvent|\PHPUnit\Framework\MockObject\MockObject
     */
    private $experienceComponentEvent;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->experienceComponentManager = $this->createMock(ExperienceComponentManager::class);
        $this->experienceComponentEvent = $this->createMock(ExperienceComponentRelationshipBroadcastEvent::class);
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals(
            [ExperienceComponentRelationshipBroadcastEvent::EVENT_NAME => ['handleMessage']],
            ExperienceComponentSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     */
    public function testHandleMessage(): void
    {
        $this->experienceComponentManager->expects($this->once())->method('replace')->willReturnSelf();
        $experienceComponentSubscriber = new ExperienceComponentSubscriber($this->logger, $this->experienceComponentManager);

        $this->assertEquals(null, $experienceComponentSubscriber->handleMessage($this->experienceComponentEvent));
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     * @covers \App\Exception\Repository\RoomNotFoundException
     * @covers \App\Exception\Repository\ExperienceNotFoundException
     * @covers \App\Contract\Request\BroadcastListener\ProductRelationshipRequest::getContext
     *
     * @dataProvider sampleException
     */
    public function testHandleMessageThrowsRoomNotFoundException(EntityNotFoundException $exception): void
    {
        $experienceComponentSubscriber = new ExperienceComponentSubscriber($this->logger, $this->experienceComponentManager);

        $this->experienceComponentManager
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
        $relationshipRequest->relationshipType = 'EXPERIENCE-COMPONENT';

        $this->experienceComponentEvent
            ->expects($this->exactly(2))
            ->method('getProductRelationshipRequest')
            ->willReturn($relationshipRequest)
        ;
        $experienceComponentSubscriber->handleMessage($this->experienceComponentEvent);
    }

    public function sampleException(): array
    {
        return [
            [new RoomNotFoundException()],
            [new ExperienceNotFoundException()],
        ];
    }
}
