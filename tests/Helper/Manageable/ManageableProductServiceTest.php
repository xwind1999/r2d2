<?php

declare(strict_types=1);

namespace App\Tests\Helper\Manageable;

use App\Contract\Request\BroadcastListener\PartnerRequest;
use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Entity\Box;
use App\Entity\Experience;
use App\Entity\Partner;
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
     * @covers ::dispatchForBox
     * @covers ::dispatchForProduct
     * @dataProvider statusProvider
     */
    public function testDispatchForBox(string $boxStatus, string $productRequestStatus): void
    {
        $box = $this->prophesize(Box::class);
        $box->status = $boxStatus;
        $productRequest = $this->prophesize(ProductRequest::class);
        $productRequest->status = $productRequestStatus;
        $this->messageBus->dispatch(Argument::any())->shouldBeCalled()->willReturn($this->envelope);
        $manageableProductService = new ManageableProductService($this->messageBus->reveal());
        $manageableProductService->dispatchForBox($productRequest->reveal(), $box->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::dispatchForExperience
     * @covers ::dispatchForProduct
     * @dataProvider statusProvider
     */
    public function testDispatchForExperience(string $experienceStatus, string $productRequestStatus): void
    {
        $experience = $this->prophesize(Experience::class);
        $experience->status = $experienceStatus;
        $productRequest = $this->prophesize(ProductRequest::class);
        $productRequest->status = $productRequestStatus;
        $this->messageBus->dispatch(Argument::any())->shouldBeCalled()->willReturn($this->envelope);
        $manageableProductService = new ManageableProductService($this->messageBus->reveal());
        $manageableProductService->dispatchForExperience($productRequest->reveal(), $experience->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::dispatchForProductRelationship
     * @covers ::dispatchForProduct
     */
    public function testDispatchForProductRelationship(): void
    {
        $productRelationshipRequest = $this->prophesize(ProductRelationshipRequest::class);
        $this->messageBus->dispatch(Argument::any())->shouldBeCalled()->willReturn($this->envelope);
        $manageableProductService = new ManageableProductService($this->messageBus->reveal());
        $manageableProductService->dispatchForProductRelationship($productRelationshipRequest->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::dispatchForPartner
     */
    public function testDispatchForPartner(): void
    {
        $partnerRequest = $this->prophesize(PartnerRequest::class);
        $partnerRequest->status = 'ceased';
        $previousPartner = $this->prophesize(Partner::class)->reveal();
        $previousPartner->status = 'partner';
        $this->messageBus->dispatch(Argument::any())->shouldBeCalled()->willReturn($this->envelope);
        $manageableProductService = new ManageableProductService($this->messageBus->reveal());
        $manageableProductService->dispatchForPartner($partnerRequest->reveal(), $previousPartner);
    }

    /**
     * @see testDispatchForBox
     * @see testDispatchForExperience
     * @see testDispatchForComponent
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
