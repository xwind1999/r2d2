<?php

declare(strict_types=1);

namespace App\Tests\Handler;

use App\Contract\Request\Manageable\ManageableProductRequest;
use App\Entity\Component;
use App\Entity\Partner;
use App\Exception\Resolver\UnprocessableManageableProductTypeException;
use App\Handler\ManageableProductHandler;
use App\Resolver\ManageableProductResolver;
use App\Tests\ProphecyTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @coversDefaultClass \App\Handler\ManageableProductHandler
 */
class ManageableProductHandlerTest extends ProphecyTestCase
{
    /**
     * @var EventDispatcherInterface|ObjectProphecy
     */
    private $eventDispatcher;

    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    /**
     * @var ManageableProductRequest|ObjectProphecy
     */
    private $manageableProductRequest;

    /**
     * @var ManageableProductResolver|ObjectProphecy
     */
    private $manageableProductResolver;

    private ManageableProductHandler $manageableBroadcastHandler;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->manageableProductRequest = $this->prophesize(ManageableProductRequest::class);
        $this->manageableProductResolver = $this->prophesize(ManageableProductResolver::class);
        $this->manageableBroadcastHandler = new ManageableProductHandler(
            $this->logger->reveal(),
            $this->eventDispatcher->reveal(),
            $this->manageableProductResolver->reveal()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::__invoke
     * @covers \App\Contract\Request\EAI\RoomRequest::transformFromComponent
     */
    public function testHandlerMessageSuccessfully(): void
    {
        $component = $this->prophesize(Component::class);
        $component->goldenId = '12345';
        $component->name = 'name';
        $component->isSellable = true;
        $component->partner = $this->prophesize(Partner::class)->reveal();
        $component->partner->goldenId = 'name';
        $component->isManageable = true;
        $component->description = 'description';
        $component->roomStockType = '2';
        $this->manageableProductResolver
            ->resolve($this->manageableProductRequest->reveal())
            ->shouldBeCalledOnce()
            ->willReturn(new Event(new \stdClass()))
        ;
        $this->logger->warning(Argument::any())->shouldNotBeCalled();
        $this->eventDispatcher->dispatch(Argument::any())->shouldBeCalledOnce()->willReturn(new Envelope(new \stdClass()));

        $this->manageableBroadcastHandler->__invoke($this->manageableProductRequest->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testHandlerMessageThrowsException(): void
    {
        $this->manageableProductResolver
            ->resolve($this->manageableProductRequest->reveal())
            ->shouldBeCalledOnce()
            ->willThrow(new UnprocessableManageableProductTypeException())
        ;
        $this->manageableProductRequest->getContext()->shouldBeCalled();
        $this->eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();
        $this->logger->warning(Argument::any(), Argument::any())->shouldBeCalledOnce();

        $this->expectException(UnprocessableManageableProductTypeException::class);
        $this->manageableBroadcastHandler->__invoke($this->manageableProductRequest->reveal());
    }
}
