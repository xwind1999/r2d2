<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\Contract\Request\BroadcastListener\RelationshipRequest;
use App\Event\ProductRelationship\ExperienceComponentEvent;
use App\EventSubscriber\ExperienceComponentSubscriber;
use App\Exception\Repository\EntityNotFoundException;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Exception\Repository\RoomNotFoundException;
use App\Handler\ProductRelationshipBroadcastHandler;
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
     * @var ExperienceComponentEvent|\PHPUnit\Framework\MockObject\MockObject
     */
    private $experienceComponentEvent;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->experienceComponentManager = $this->createMock(ExperienceComponentManager::class);
        $this->experienceComponentEvent = $this->createMock(ExperienceComponentEvent::class);
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals(
            [ProductRelationshipBroadcastHandler::EXPERIENCE_COMPONENT_EVENT => ['handleMessage']],
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
     *
     * @dataProvider sampleExcepetion
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

        $relationshipRequest = $this->createMock(RelationshipRequest::class);
        $relationshipRequest->childProduct = '111';
        $relationshipRequest->parentProduct = '222';

        $this->experienceComponentEvent
            ->expects($this->exactly(2))
            ->method('getRelationshipRequest')
            ->willReturn($relationshipRequest)
        ;
        $experienceComponentSubscriber->handleMessage($this->experienceComponentEvent);
    }

    public function sampleExcepetion(): array
    {
        return [
            [new RoomNotFoundException()],
            [new ExperienceNotFoundException()],
        ];
    }
}
