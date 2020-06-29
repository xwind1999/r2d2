<?php

declare(strict_types=1);

namespace App\Tests\Command\Import\Helper\Manageable;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Helper\Manageable\ManageableProductService;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \App\Helper\Manageable\ManageableProductService
 */
class ManageableProductServiceTest extends KernelTestCase
{
    /**
     * @var MessageBusInterface|ObjectProphecy
     */
    private $messageBus;

    private Envelope $envelope;

    protected function setUp(): void
    {
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->envelope = new Envelope(new \stdClass());
    }

    /**
     * @covers ::__construct
     * @covers ::dispatchForProduct
     * @dataProvider statusProvider
     */
    public function testDispatchForProduct(string $status, string $productRequestStatus): void
    {
        $productRequest = $this->prophesize(ProductRequest::class);
        $this->messageBus->dispatch(Argument::any())->shouldBeCalled()->willReturn($this->envelope);
        $productRequest->status = $productRequestStatus;
        $manageableProductService = new ManageableProductService($this->messageBus->reveal());
        $this->assertEmpty($manageableProductService->dispatchForProduct($productRequest->reveal(), $status));
    }

    /**
     * @covers ::__construct
     * @covers ::dispatchForProductRelationship
     */
    public function testDispatchForProductRelationship(): void
    {
        $productRelationshipRequest = $this->prophesize(ProductRelationshipRequest::class);

        $this->messageBus->dispatch(Argument::any())->shouldBeCalled()->willReturn($this->envelope);
        $manageableProductService = new ManageableProductService($this->messageBus->reveal());
        $this->assertEmpty($manageableProductService->dispatchForProductRelationship($productRelationshipRequest->reveal()));
    }

    /**
     * @see testIsDispatch
     */
    public function statusProvider(): array
    {
        return [
            ['prospect', 'live'],
            ['production', 'obsolete'],
            ['live', 'active'],
            ['obsolete', 'inactive'],
            ['active', 'redeemable'],
            ['inactive', 'ready'],
            ['redeemable', 'prospect'],
            ['ready', 'production'],
        ];
    }
}
