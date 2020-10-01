<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Message\CalculateFlatManageableComponent;
use App\Contract\Request\BroadcastListener\Product\Partner as PartnerDTO;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\EAI\RoomRequest;
use App\Contract\Request\Internal\Component\ComponentCreateRequest;
use App\Contract\Request\Internal\Component\ComponentUpdateRequest;
use App\Entity\Component;
use App\Entity\Partner;
use App\Exception\Manager\Component\OutdatedComponentException;
use App\Exception\Repository\ComponentNotFoundException;
use App\Exception\Repository\ManageableProductNotFoundException;
use App\Exception\Repository\PartnerNotFoundException;
use App\Helper\Manageable\ManageableProductService;
use App\Manager\ComponentManager;
use App\Manager\PartnerManager;
use App\Repository\ComponentRepository;
use App\Repository\PartnerRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \App\Manager\ComponentManager
 */
class ComponentManagerTest extends TestCase
{
    /**
     * @var ComponentRepository|ObjectProphecy
     */
    protected $repository;

    /**
     * @var ObjectProphecy|PartnerRepository
     */
    protected $partnerRepository;

    /**
     * @var ManageableProductService|ObjectProphecy
     */
    private $manageableProductService;

    /**
     * @var ObjectProphecy|PartnerManager
     */
    private $partnerManager;

    /**
     * @var MessageBusInterface|ObjectProphecy
     */
    private $messageBus;

    private ComponentManager $manager;

    public function setUp(): void
    {
        $this->repository = $this->prophesize(ComponentRepository::class);
        $this->partnerRepository = $this->prophesize(PartnerRepository::class);
        $this->manageableProductService = $this->prophesize(ManageableProductService::class);
        $this->partnerManager = $this->prophesize(PartnerManager::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->manager = new ComponentManager(
            $this->repository->reveal(),
            $this->partnerRepository->reveal(),
            $this->manageableProductService->reveal(),
            $this->partnerManager->reveal(),
            $this->messageBus->reveal()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::update
     */
    public function testUpdate(): void
    {
        $partner = new Partner();
        $partner->goldenId = '4321';
        $this->partnerRepository->findOneByGoldenId('4321')->willReturn($partner);
        $componentUpdateRequest = $this->prophesize(ComponentUpdateRequest::class);
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $componentUpdateRequest->uuid = $uuid;
        $componentUpdateRequest->goldenId = '1234';
        $componentUpdateRequest->partnerGoldenId = '4321';
        $componentUpdateRequest->name = 'room with a big big bed';
        $componentUpdateRequest->description = 'the bed is so big it could fit two families';
        $componentUpdateRequest->inventory = 2;
        $componentUpdateRequest->isSellable = true;
        $componentUpdateRequest->duration = 3;
        $componentUpdateRequest->durationUnit = 'minute';
        $componentUpdateRequest->isReservable = true;
        $componentUpdateRequest->status = 'not_ok';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);

        $component = $this->prophesize(Component::class);
        $component->uuid = $uuidInterface->reveal();
        $component->goldenId = '5678';
        $component->partnerGoldenId = '5678';
        $component->name = 'room with small bed';
        $component->description = 'the bed is very small';
        $component->inventory = 1;
        $component->duration = 2;
        $component->durationUnit = 'day';
        $component->isSellable = false;
        $component->isReservable = false;
        $component->status = 'ok';
        $this->repository->findOne($uuid)->willReturn($component->reveal());

        $this->repository->save(Argument::type(Component::class))->shouldBeCalled();

        $this->manager->update($uuid, $componentUpdateRequest->reveal());

        $this->assertEquals(2, $component->inventory);
        $this->assertEquals(3, $component->duration);
        $this->assertEquals(true, $component->isReservable);
        $this->assertEquals(true, $component->isSellable);
        $this->assertEquals('minute', $component->durationUnit);
        $this->assertEquals('4321', $component->partnerGoldenId);
        $this->assertEquals('room with a big big bed', $component->name);
        $this->assertEquals('the bed is so big it could fit two families', $component->description);
        $this->assertEquals('1234', $component->goldenId);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::delete
     */
    public function testDelete(): void
    {
        $uuid = '12345678';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $component = $this->prophesize(Component::class);
        $component->uuid = $uuidInterface->reveal();
        $this->repository->findOne($uuid)->willReturn($component->reveal());
        $this->repository->delete(Argument::type(Component::class))->shouldBeCalled();

        $this->manager->delete($uuid);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreate(): void
    {
        $partner = $this->prophesize(Partner::class);
        $partner->goldenId = '5678';
        $this->partnerRepository->findOneByGoldenId('5678')->willReturn($partner->reveal());
        $componentCreateRequest = $this->prophesize(ComponentCreateRequest::class);
        $componentCreateRequest->goldenId = '5678';
        $componentCreateRequest->partnerGoldenId = '5678';
        $componentCreateRequest->name = 'room with small bed';
        $componentCreateRequest->description = 'the bed is very small';
        $componentCreateRequest->inventory = 1;
        $componentCreateRequest->duration = 2;
        $componentCreateRequest->durationUnit = 'day';
        $componentCreateRequest->isSellable = false;
        $componentCreateRequest->isReservable = false;
        $componentCreateRequest->status = 'ok';

        $this->repository->save(Argument::type(Component::class))->shouldBeCalled();

        $component = $this->manager->create($componentCreateRequest->reveal());
        $this->assertEquals($componentCreateRequest->goldenId, $component->goldenId);
        $this->assertEquals($componentCreateRequest->partnerGoldenId, $component->partnerGoldenId);
        $this->assertEquals($componentCreateRequest->name, $component->name);
        $this->assertEquals($componentCreateRequest->description, $component->description);
        $this->assertEquals($componentCreateRequest->inventory, $component->inventory);
        $this->assertEquals($componentCreateRequest->duration, $component->duration);
        $this->assertEquals($componentCreateRequest->durationUnit, $component->durationUnit);
        $this->assertEquals($componentCreateRequest->isSellable, $component->isSellable);
        $this->assertEquals($componentCreateRequest->isReservable, $component->isReservable);
        $this->assertEquals($componentCreateRequest->status, $component->status);
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplace(): void
    {
        $partner = $this->prophesize(PartnerDTO::class);
        $partner->id = '5678';
        $productRequest = $this->prophesize(ProductRequest::class);
        $productRequest->id = '5678';
        $productRequest->partner = $partner->reveal();
        $productRequest->name = 'dinner with massage';
        $productRequest->description = 'a fancy dinner with feet massage';
        $productRequest->productDuration = 2;
        $productRequest->productDurationUnit = 'day';
        $productRequest->isSellable = true;
        $productRequest->isReservable = true;
        $productRequest->status = 'test Status';
        $productRequest->roomStockType = 'on_request';
        $component = $this->prophesize(Component::class);
        $component->status = 'active';
        $component->isReservable = true;

        $this->partnerRepository->findOneByGoldenId($productRequest->partner->id);
        $this->repository->findOneByGoldenId($productRequest->id)->willReturn($component->reveal());
        $this->repository->save(Argument::type(Component::class))->shouldBeCalled();
        $this->manageableProductService->dispatchForProduct(Argument::any())->shouldBeCalled();
        $this->manager->replace($productRequest->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplaceWithPlaceholderPartner(): void
    {
        $partner = $this->prophesize(PartnerDTO::class);
        $partner->id = '5678';
        $productRequest = $this->prophesize(ProductRequest::class);
        $productRequest->id = '5678';
        $productRequest->partner = $partner->reveal();
        $productRequest->name = 'dinner with massage';
        $productRequest->description = 'a fancy dinner with feet massage';
        $productRequest->productDuration = 2;
        $productRequest->productDurationUnit = 'day';
        $productRequest->isSellable = true;
        $productRequest->isReservable = true;
        $productRequest->status = 'test Status';
        $productRequest->roomStockType = 'on_request';
        $component = $this->prophesize(Component::class);
        $component->status = 'active';
        $component->isReservable = true;

        $partnerEntity = new Partner();
        $partnerEntity->goldenId = '5678';
        $this->partnerRepository->findOneByGoldenId($productRequest->partner->id)->willThrow(new PartnerNotFoundException());
        $this->partnerManager->createPlaceholder($productRequest->partner->id)->shouldBeCalled()->willReturn($partnerEntity);
        $this->repository->findOneByGoldenId($productRequest->id)->willReturn($component->reveal());
        $this->repository->save(Argument::type(Component::class))->shouldBeCalled();
        $this->manageableProductService->dispatchForProduct(Argument::any(), Argument::any())->shouldBeCalled();
        $this->manager->replace($productRequest->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplaceWithOutdatedRecord(): void
    {
        $partner = $this->prophesize(PartnerDTO::class);
        $partner->id = '5678';
        $productRequest = new ProductRequest();
        $productRequest->id = '5678';
        $productRequest->partner = $partner->reveal();
        $productRequest->updatedAt = new \DateTime('2020-01-01 00:00:00');

        $component = $this->prophesize(Component::class);
        $component->externalUpdatedAt = new \DateTime('2020-01-01 01:00:00');

        $this->partnerRepository->findOneByGoldenId($productRequest->partner->id);
        $this->repository->findOneByGoldenId($productRequest->id)->willReturn($component->reveal());
        $this->expectException(OutdatedComponentException::class);

        $this->manager->replace($productRequest);
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplaceCatchesExperienceNotFoundException(): void
    {
        $partner = $this->prophesize(PartnerDTO::class);
        $partner->id = '5678';
        $productRequest = $this->prophesize(ProductRequest::class);
        $productRequest->id = '5678';
        $productRequest->partner = $partner->reveal();
        $productRequest->name = 'dinner with massage';
        $productRequest->description = 'a fancy dinner with feet massage';
        $productRequest->isSellable = true;
        $productRequest->isReservable = true;
        $productRequest->status = 'test Status';
        $productRequest->roomStockType = 'allotment';

        $this->partnerRepository->findOneByGoldenId($productRequest->id);
        $this->repository
            ->findOneByGoldenId($productRequest->id)
            ->shouldBeCalled()
            ->willThrow(new ComponentNotFoundException())
        ;
        $this->repository->save(Argument::type(Component::class))->shouldBeCalled();

        $this->manager->replace($productRequest->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::calculateManageableFlag
     * @covers ::createComponentRequiredCriteria
     * @covers ::createManageableCriteria
     */
    public function testFindAndSetManageableComponent(): void
    {
        $component = $this->prophesize(Component::class);
        $component->goldenId = '12345';
        $component->name = '123';
        $component->isSellable = false;
        $component->isManageable = false;
        $partner = new Partner();
        $partner->goldenId = '1234';
        $component->partner = $partner;
        $this->repository
            ->findComponentWithManageableCriteria(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($component->reveal())
        ;
        $this->repository
            ->findComponentWithManageableRelationships(Argument::any())
            ->shouldNotBeCalled();
        $this->repository->save(Argument::type(Component::class))->shouldBeCalledOnce();
        $this->messageBus->dispatch(Argument::type(RoomRequest::class))->shouldBeCalled()->willReturn(new Envelope(new \stdClass()));
        (function ($test, $goldenId) {
            $this->messageBus->dispatch(Argument::type(CalculateFlatManageableComponent::class))->will(function ($args) use ($test, $goldenId) {
                $test->assertEquals($goldenId, $args[0]->componentGoldenId);

                return new Envelope(new \stdClass());
            })
            ->shouldBeCalled();
        })($this, $component->goldenId);

        $this->manager->calculateManageableFlag($component->goldenId);
        $this->assertTrue($component->isManageable);
    }

    /**
     * @covers ::__construct
     * @covers ::calculateManageableFlag
     * @covers ::createComponentRequiredCriteria
     * @covers ::createManageableCriteria
     */
    public function testFindAndSetManageableComponentWontChangeAnything(): void
    {
        $component = $this->prophesize(Component::class);
        $component->goldenId = '12345';
        $component->isManageable = true;
        $component->name = '123';
        $component->isSellable = false;
        $partner = new Partner();
        $partner->goldenId = '1234';
        $component->partner = $partner;
        $this->repository
            ->findComponentWithManageableCriteria(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($component->reveal())
        ;
        $this->repository
            ->findComponentWithManageableRelationships(Argument::any())
            ->shouldNotBeCalled();
        $this->repository->save(Argument::type(Component::class))->shouldNotBeCalled();
        $this->messageBus->dispatch(Argument::type(RoomRequest::class))->shouldBeCalled()->willReturn(new Envelope(new \stdClass()));
        (function ($test, $goldenId) {
            $this->messageBus->dispatch(Argument::type(CalculateFlatManageableComponent::class))->will(function ($args) use ($test, $goldenId) {
                $test->assertEquals($goldenId, $args[0]->componentGoldenId);

                return new Envelope(new \stdClass());
            })
                ->shouldBeCalled();
        })($this, $component->goldenId);

        $this->manager->calculateManageableFlag($component->goldenId);
        $this->assertTrue($component->isManageable);
    }

    /**
     * @covers ::__construct
     * @covers ::calculateManageableFlag
     * @covers ::createComponentRequiredCriteria
     * @covers ::createManageableCriteria
     */
    public function testFindAndSetManageableComponentCatchesManageableProductNotFoundException(): void
    {
        $component = $this->prophesize(Component::class);
        $component->goldenId = '12345';
        $component->isManageable = true;
        $component->name = '123';
        $component->isSellable = false;
        $partner = new Partner();
        $partner->goldenId = '1234';
        $component->partner = $partner;
        $this->repository
            ->findComponentWithManageableCriteria(Argument::any())
            ->shouldBeCalledOnce()
            ->willThrow(ManageableProductNotFoundException::class)
        ;
        $this->repository
            ->findComponentWithManageableRelationships(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($component->reveal())
        ;
        $this->repository->save(Argument::type(Component::class))->shouldBeCalledOnce();
        $this->messageBus->dispatch(Argument::type(RoomRequest::class))->shouldBeCalled()->willReturn(new Envelope(new \stdClass()));
        (function ($test, $goldenId) {
            $this->messageBus->dispatch(Argument::type(CalculateFlatManageableComponent::class))->will(function ($args) use ($test, $goldenId) {
                $test->assertEquals($goldenId, $args[0]->componentGoldenId);

                return new Envelope(new \stdClass());
            })
                ->shouldBeCalled();
        })($this, $component->goldenId);

        $this->manager->calculateManageableFlag($component->goldenId);

        $this->assertFalse($component->isManageable);
    }

    /**
     * @covers ::__construct
     * @covers ::calculateManageableFlag
     * @covers ::createComponentRequiredCriteria
     * @covers ::createManageableCriteria
     */
    public function testFindAndSetManageableComponentCatchesManageableProductNotFoundExceptionAndWontChangeAnything(): void
    {
        $component = $this->prophesize(Component::class);
        $component->goldenId = '12345';
        $component->isManageable = false;
        $component->name = '123';
        $component->isSellable = false;
        $partner = new Partner();
        $partner->goldenId = '1234';
        $component->partner = $partner;
        $this->repository
            ->findComponentWithManageableCriteria(Argument::any())
            ->shouldBeCalledOnce()
            ->willThrow(ManageableProductNotFoundException::class)
        ;
        $this->repository
            ->findComponentWithManageableRelationships(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($component->reveal())
        ;
        $this->repository->save(Argument::type(Component::class))->shouldNotBeCalled();
        $this->messageBus->dispatch(Argument::type(RoomRequest::class))->shouldNotBeCalled();
        (function ($test, $goldenId) {
            $this->messageBus->dispatch(Argument::type(CalculateFlatManageableComponent::class))->will(function ($args) use ($test, $goldenId) {
                $test->assertEquals($goldenId, $args[0]->componentGoldenId);

                return new Envelope(new \stdClass());
            })
                ->shouldBeCalled();
        })($this, $component->goldenId);

        $this->manager->calculateManageableFlag($component->goldenId);

        $this->assertFalse($component->isManageable);
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomsByExperienceGoldenIdsList
     */
    public function testGetRoomsByExperienceGoldenIdsList(): void
    {
        $compIds = [
            '1234', '4321', '1111',
        ];
        $this->repository->findRoomsByExperienceGoldenIdsList(Argument::any())->willReturn($compIds);
        $manager = new ComponentManager(
            $this->repository->reveal(),
            $this->partnerRepository->reveal(),
            $this->manageableProductService->reveal(),
            $this->partnerManager->reveal(),
            $this->messageBus->reveal()
        );
        $manager->getRoomsByExperienceGoldenIdsList($compIds);
        $this->repository->findRoomsByExperienceGoldenIdsList($compIds)->shouldBeCalledOnce();
    }
}
