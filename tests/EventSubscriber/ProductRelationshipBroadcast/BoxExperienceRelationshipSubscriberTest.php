<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber\ProductRelationshipBroadcast;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Event\ProductRelationship\BoxExperienceRelationshipBroadcastEvent;
use App\EventSubscriber\ProductRelationshipBroadcast\BoxExperienceRelationshipSubscriber;
use App\Exception\Manager\BoxExperience\OutdatedBoxExperienceRelationshipException;
use App\Exception\Repository\BoxNotFoundException;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Manager\BoxExperienceManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\EventSubscriber\ProductRelationshipBroadcast\BoxExperienceRelationshipSubscriber
 */
class BoxExperienceRelationshipSubscriberTest extends TestCase
{
    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var BoxExperienceManager|MockObject
     */
    private $boxExperienceManager;

    /**
     * @var BoxExperienceRelationshipBroadcastEvent|MockObject
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
            [BoxExperienceRelationshipBroadcastEvent::class => ['handleMessage']],
            BoxExperienceRelationshipSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     */
    public function testHandleMessage(): void
    {
        $this->boxExperienceManager->expects($this->once())->method('replace')->willReturnSelf();
        $boxExperienceSubscriber = new BoxExperienceRelationshipSubscriber($this->logger, $this->boxExperienceManager);

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
    public function testHandleMessageThrowsBoxNotFoundException(\Exception $exception, string $logLevel): void
    {
        $boxExperienceSubscriber = new BoxExperienceRelationshipSubscriber($this->logger, $this->boxExperienceManager);

        $this->boxExperienceManager
            ->expects($this->once())
            ->method('replace')
            ->willThrowException($exception)
        ;
        $this->logger->expects($this->once())->method($logLevel)->willReturn(null);

        $relationshipRequest = new ProductRelationshipRequest();
        $relationshipRequest->childProduct = '111';
        $relationshipRequest->parentProduct = '222';
        $relationshipRequest->isEnabled = true;
        $relationshipRequest->relationshipType = 'BOX-EXPERIENCE';

        $this->boxExperienceEvent
            ->expects($this->exactly(2))
            ->method('getProductRelationshipRequest')
            ->willReturn($relationshipRequest)
        ;
        $this->expectException(get_class($exception));
        $boxExperienceSubscriber->handleMessage($this->boxExperienceEvent);
    }

    public function sampleException(): array
    {
        return [
            [new BoxNotFoundException(), 'warning'],
            [new OutdatedBoxExperienceRelationshipException(), 'warning'],
            [new ExperienceNotFoundException(), 'warning'],
            [new \Exception(), 'error'],
        ];
    }
}
