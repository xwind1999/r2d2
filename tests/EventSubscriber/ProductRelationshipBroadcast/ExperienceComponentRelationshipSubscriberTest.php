<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Event\ProductRelationship\ExperienceComponentRelationshipBroadcastEvent;
use App\EventSubscriber\ProductRelationshipBroadcast\ExperienceComponentRelationshipSubscriber;
use App\Exception\Manager\ExperienceComponent\OutdatedExperienceComponentRelationshipException;
use App\Exception\Repository\ComponentNotFoundException;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Manager\ExperienceComponentManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\EventSubscriber\ProductRelationshipBroadcast\ExperienceComponentRelationshipSubscriber
 */
class ExperienceComponentRelationshipSubscriberTest extends TestCase
{
    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var ExperienceComponentManager|MockObject
     */
    private $experienceComponentManager;

    /**
     * @var ExperienceComponentRelationshipBroadcastEvent|MockObject
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
            [ExperienceComponentRelationshipBroadcastEvent::class => ['handleMessage']],
            ExperienceComponentRelationshipSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     */
    public function testHandleMessage(): void
    {
        $this->experienceComponentManager->expects($this->once())->method('replace')->willReturnSelf();
        $experienceComponentSubscriber = new ExperienceComponentRelationshipSubscriber($this->logger, $this->experienceComponentManager);

        $this->assertEquals(null, $experienceComponentSubscriber->handleMessage($this->experienceComponentEvent));
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     * @covers \App\Exception\Repository\ComponentNotFoundException
     * @covers \App\Exception\Repository\ExperienceNotFoundException
     * @covers \App\Contract\Request\BroadcastListener\ProductRelationshipRequest::getContext
     *
     * @dataProvider sampleException
     */
    public function testHandleMessageThrowsRoomNotFoundException(\Exception $exception, string $logLevel): void
    {
        $experienceComponentSubscriber = new ExperienceComponentRelationshipSubscriber($this->logger, $this->experienceComponentManager);

        $this->experienceComponentManager
            ->expects($this->once())
            ->method('replace')
            ->willThrowException($exception)
        ;
        $this->logger->expects($this->once())->method($logLevel)->willReturn(null);

        $relationshipRequest = new ProductRelationshipRequest();
        $relationshipRequest->childProduct = '111';
        $relationshipRequest->parentProduct = '222';
        $relationshipRequest->isEnabled = true;
        $relationshipRequest->relationshipType = 'EXPERIENCE-COMPONENT';

        $this->experienceComponentEvent
            ->expects($this->exactly(2))
            ->method('getProductRelationshipRequest')
            ->willReturn($relationshipRequest)
        ;

        $this->expectException(get_class($exception));
        $experienceComponentSubscriber->handleMessage($this->experienceComponentEvent);
    }

    public function sampleException(): array
    {
        return [
            [new ComponentNotFoundException(), 'warning'],
            [new OutdatedExperienceComponentRelationshipException(), 'warning'],
            [new ExperienceNotFoundException(), 'warning'],
            [new \Exception(), 'error'],
        ];
    }
}
