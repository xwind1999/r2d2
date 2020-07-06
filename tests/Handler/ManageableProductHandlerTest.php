<?php

declare(strict_types=1);

namespace App\Tests\Handler;

use App\Contract\Request\Manageable\ManageableProductRequest;
use App\Entity\Component;
use App\Entity\Partner;
use App\Exception\Resolver\UnprocessableManageableProductTypeException;
use App\Handler\ManageableProductHandler;
use App\Manager\ComponentManager;
use App\Resolver\ManageableProductResolver;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \App\Handler\ManageableProductHandler
 */
class ManageableProductHandlerTest extends TestCase
{
    /**
     * @var MessageBusInterface|ObjectProphecy
     */
    private $messageBus;

    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    /**
     * @var ComponentManager|ObjectProphecy
     */
    private $componentManager;

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
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->componentManager = $this->prophesize(ComponentManager::class);
        $this->manageableProductRequest = $this->prophesize(ManageableProductRequest::class);
        $this->manageableProductResolver = $this->prophesize(ManageableProductResolver::class);
        $this->manageableBroadcastHandler = new ManageableProductHandler(
            $this->logger->reveal(),
            $this->componentManager->reveal(),
            $this->messageBus->reveal(),
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
            ->willReturn($this->manageableProductRequest->reveal())
        ;
        $this->componentManager
            ->findAndSetManageableComponent($this->manageableProductRequest->reveal())
            ->shouldBeCalledOnce()
            ->willReturn($component->reveal())
        ;
        $event = new Envelope(new \stdClass());
        $this->logger->warning(Argument::any())->shouldNotBeCalled();
        $this->messageBus->dispatch(Argument::any())->shouldBeCalledOnce()->willReturn($event);

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
            ->willThrow(UnprocessableManageableProductTypeException::class)
        ;
        $this->componentManager
            ->findAndSetManageableComponent($this->manageableProductRequest->reveal())
            ->shouldNotBeCalled()
        ;
        $this->manageableProductRequest->getContext()->shouldBeCalled();
        $this->messageBus->dispatch(Argument::any())->shouldNotBeCalled();
        $this->logger->warning(Argument::any(), Argument::any())->shouldBeCalledOnce();

        $this->manageableBroadcastHandler->__invoke($this->manageableProductRequest->reveal());
    }
}
