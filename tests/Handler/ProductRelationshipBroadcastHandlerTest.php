<?php

declare(strict_types=1);

namespace App\Tests\Handler;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Event\ProductRelationship\ExperienceComponentRelationshipBroadcastEvent;
use App\Exception\Resolver\UnprocessableProductRelationshipTypeException;
use App\Handler\ProductRelationshipBroadcastHandler;
use App\Resolver\ProductRelationshipTypeResolver;
use phpDocumentor\Reflection\Types\Void_;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @coversDefaultClass \App\Handler\ProductRelationshipBroadcastHandler
 */
class ProductRelationshipBroadcastHandlerTest extends TestCase
{
    /**
     * @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $logger;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ProductRelationshipTypeResolver
     */
    private $productRelationshipTypeResolver;

    /**
     * @var EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eventDispatcher;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->productRelationshipTypeResolver = $this->createMock(ProductRelationshipTypeResolver::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
    }

    /**
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testHandlerMessageSuccessfully(): void
    {
        $parentProduct = '860eb3100e3689e57b5d9772';
        $childProduct = 'cde730104afb457781d05436';

        $relationshipRequest = new ProductRelationshipRequest();
        $relationshipRequest->parentProduct = $parentProduct;
        $relationshipRequest->childProduct = $childProduct;
        $relationshipRequest->isEnabled = true;
        $relationshipRequest->relationshipType = 'Experience-Component';

        $productRelationshipBroadcastHandler = new ProductRelationshipBroadcastHandler(
            $this->logger,
            $this->productRelationshipTypeResolver,
            $this->eventDispatcher
        );
        $envelope = $this->createMock(ExperienceComponentRelationshipBroadcastEvent::class);

        $this->eventDispatcher->expects($this->once())->method('dispatch')->willReturn($envelope);
        $this->assertEquals(null, $productRelationshipBroadcastHandler->__invoke($relationshipRequest));
    }

    /**
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testHandlerMessageThrowsNonExistentTypeResolverException(): void
    {
        $parentProduct = '860eb3100e3689e57b5d9772';
        $childProduct = 'cde730104afb457781d05436';

        $relationshipRequest = new ProductRelationshipRequest();
        $relationshipRequest->parentProduct = $parentProduct;
        $relationshipRequest->childProduct = $childProduct;
        $relationshipRequest->isEnabled = true;
        $relationshipRequest->relationshipType = 'Component-Experience';

        $productRelationshipBroadcastHandler = new ProductRelationshipBroadcastHandler(
            $this->logger,
            $this->productRelationshipTypeResolver,
            $this->eventDispatcher
        );

        $this->productRelationshipTypeResolver
            ->expects($this->once())
            ->method('resolve')
            ->willThrowException(new UnprocessableProductRelationshipTypeException())
        ;
        $this->logger->expects($this->once())->method('warning')->willReturn(Void_::class);

        $this->assertEquals(null, $productRelationshipBroadcastHandler->__invoke($relationshipRequest));
    }
}
