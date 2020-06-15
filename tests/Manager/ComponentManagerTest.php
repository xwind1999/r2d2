<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\BroadcastListener\Product\Partner as PartnerDTO;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Internal\Component\ComponentCreateRequest;
use App\Contract\Request\Internal\Component\ComponentUpdateRequest;
use App\Entity\Component;
use App\Entity\Partner;
use App\Exception\Manager\Component\OutdatedComponentException;
use App\Exception\Repository\ComponentNotFoundException;
use App\Manager\ComponentManager;
use App\Repository\ComponentRepository;
use App\Repository\PartnerRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\UuidInterface;

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

    public function setUp(): void
    {
        $this->repository = $this->prophesize(ComponentRepository::class);
        $this->partnerRepository = $this->prophesize(PartnerRepository::class);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::update
     */
    public function testUpdate()
    {
        $manager = new ComponentManager($this->repository->reveal(), $this->partnerRepository->reveal());
        $partner = new Partner();
        $partner->goldenId = '4321';
        $this->partnerRepository->findOneByGoldenId('4321')->willReturn($partner);
        $componentUpdateRequest = new ComponentUpdateRequest();
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

        $component = new Component();
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
        $this->repository->findOne($uuid)->willReturn($component);

        $this->repository->save(Argument::type(Component::class))->shouldBeCalled();

        $updatedRoom = $manager->update($uuid, $componentUpdateRequest);

        $this->assertSame($component, $updatedRoom);
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
    public function testDelete()
    {
        $manager = new ComponentManager($this->repository->reveal(), $this->partnerRepository->reveal());
        $uuid = '12345678';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $component = new Component();
        $component->uuid = $uuidInterface->reveal();
        $this->repository->findOne($uuid)->willReturn($component);

        $this->repository->delete(Argument::type(Component::class))->shouldBeCalled();

        $manager->delete($uuid);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreate()
    {
        $manager = new ComponentManager($this->repository->reveal(), $this->partnerRepository->reveal());
        $partner = new Partner();
        $partner->goldenId = '5678';
        $this->partnerRepository->findOneByGoldenId('5678')->willReturn($partner);
        $componentCreateRequest = new ComponentCreateRequest();
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

        $component = $manager->create($componentCreateRequest);
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
    public function testReplace()
    {
        $manager = new ComponentManager($this->repository->reveal(), $this->partnerRepository->reveal());
        $partner = new PartnerDTO();
        $partner->id = '5678';
        $productRequest = new ProductRequest();
        $productRequest->id = '5678';
        $productRequest->partner = $partner;
        $productRequest->name = 'dinner with massage';
        $productRequest->description = 'a fancy dinner with feet massage';
        $productRequest->productDuration = 2;
        $productRequest->productDurationUnit = 'day';
        $productRequest->isSellable = true;
        $productRequest->isReservable = true;
        $productRequest->status = 'test Status';
        $productRequest->roomStockType = 'on_request';

        $this->partnerRepository->findOneByGoldenId($productRequest->partner->id);
        $this->repository->findOneByGoldenId($productRequest->id);

        $this->repository->save(Argument::type(Component::class))->shouldBeCalled();

        $this->assertEmpty($manager->replace($productRequest));
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplaceWithOutdatedRecord()
    {
        $manager = new ComponentManager($this->repository->reveal(), $this->partnerRepository->reveal());
        $partner = new PartnerDTO();
        $partner->id = '5678';
        $productRequest = new ProductRequest();
        $productRequest->id = '5678';
        $productRequest->partner = $partner;
        $productRequest->updatedAt = new \DateTime('2020-01-01 00:00:00');

        $component = new Component();
        $component->externalUpdatedAt = new \DateTime('2020-01-01 01:00:00');

        $this->partnerRepository->findOneByGoldenId($productRequest->partner->id);
        $this->repository->findOneByGoldenId($productRequest->id)->willReturn($component);

        $this->expectException(OutdatedComponentException::class);

        $manager->replace($productRequest);
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplaceCatchesExperienceNotFoundException()
    {
        $manager = new ComponentManager($this->repository->reveal(), $this->partnerRepository->reveal());
        $partner = new PartnerDTO();
        $partner->id = '5678';
        $productRequest = new ProductRequest();
        $productRequest->id = '5678';
        $productRequest->partner = $partner;
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

        $this->assertEmpty($manager->replace($productRequest));
    }
}
