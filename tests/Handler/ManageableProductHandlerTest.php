<?php

declare(strict_types=1);

namespace App\Tests\Handler;

use App\Contract\Request\Manageable\ManageableProductRequest;
use App\Exception\Resolver\UnprocessableManageableProductTypeException;
use App\Handler\ManageableProductHandler;
use App\Manager\ComponentManager;
use App\Resolver\ManageableProductResolver;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\Handler\ManageableProductHandler
 */
class ManageableProductHandlerTest extends TestCase
{
    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    /**
     * @var ManageableProductResolver|ObjectProphecy
     */
    private $manageableProductResolver;

    /**
     * @var ComponentManager|ObjectProphecy
     */
    private $componentManager;

    /**
     * @var ManageableProductRequest|ObjectProphecy
     */
    private $manageableProductRequest;

    protected function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->manageableProductResolver = $this->prophesize(ManageableProductResolver::class);
        $this->componentManager = $this->prophesize(ComponentManager::class);
        $this->manageableProductRequest = $this->prophesize(ManageableProductRequest::class);
    }

    /**
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testHandlerMessageSuccessfully(): void
    {
        $this->manageableProductResolver
            ->resolve($this->manageableProductRequest->reveal())
            ->shouldBeCalledOnce()
            ->willReturn($this->manageableProductRequest->reveal())
        ;
        $this->componentManager
            ->findAndSetManageableComponent($this->manageableProductRequest->reveal())
            ->shouldBeCalledOnce()
        ;
        $this->logger->warning(Argument::any())->shouldNotBeCalled();
        $productBroadcastHandler = new ManageableProductHandler(
            $this->logger->reveal(),
            $this->manageableProductResolver->reveal(),
            $this->componentManager->reveal())
        ;
        $productBroadcastHandler->__invoke($this->manageableProductRequest->reveal());
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
        $this->logger->warning(Argument::any(), Argument::any())->shouldBeCalledOnce();
        $productBroadcastHandler = new ManageableProductHandler(
            $this->logger->reveal(),
            $this->manageableProductResolver->reveal(),
            $this->componentManager->reveal())
        ;
        $productBroadcastHandler->__invoke($this->manageableProductRequest->reveal());
    }
}
