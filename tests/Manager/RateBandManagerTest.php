<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\Internal\RateBand\RateBandCreateRequest;
use App\Contract\Request\Internal\RateBand\RateBandUpdateRequest;
use App\Entity\Partner;
use App\Entity\RateBand;
use App\Manager\RateBandManager;
use App\Repository\PartnerRepository;
use App\Repository\RateBandRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Manager\RateBandManager
 */
class RateBandManagerTest extends TestCase
{
    /**
     * @var ObjectProphecy|RateBandRepository
     */
    protected $repository;

    /**
     * @var ObjectProphecy|PartnerRepository
     */
    protected $partnerRepository;

    public function setUp(): void
    {
        $this->repository = $this->prophesize(RateBandRepository::class);
        $this->partnerRepository = $this->prophesize(PartnerRepository::class);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::update
     */
    public function testUpdate()
    {
        $manager = new RateBandManager($this->repository->reveal(), $this->partnerRepository->reveal());
        $partner = new Partner();
        $partner->goldenId = '4321';
        $this->partnerRepository->findOneByGoldenId('4321')->willReturn($partner);
        $rateBandUpdateRequest = new RateBandUpdateRequest();
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $rateBandUpdateRequest->uuid = $uuid;
        $rateBandUpdateRequest->goldenId = '1234';
        $rateBandUpdateRequest->partnerGoldenId = '4321';
        $rateBandUpdateRequest->name = 'testName';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);

        $rateBand = new RateBand();
        $rateBand->uuid = $uuidInterface->reveal();
        $rateBand->goldenId = '1234';
        $rateBand->partnerGoldenId = '4321';
        $rateBand->name = 'testName';
        $this->repository->findOne($uuid)->willReturn($rateBand);

        $this->repository->save(Argument::type(RateBand::class))->shouldBeCalled();

        $updatedRateBand = $manager->update($uuid, $rateBandUpdateRequest);

        $this->assertSame($rateBand, $updatedRateBand);
        $this->assertEquals('1234', $rateBand->goldenId);
        $this->assertEquals('4321', $rateBand->partnerGoldenId);
        $this->assertEquals('testName', $rateBand->name);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::delete
     */
    public function testDelete()
    {
        $manager = new RateBandManager($this->repository->reveal(), $this->partnerRepository->reveal());
        $uuid = '12345678';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $rateBand = new RateBand();
        $rateBand->uuid = $uuidInterface->reveal();
        $this->repository->findOne($uuid)->willReturn($rateBand);

        $this->repository->delete(Argument::type(RateBand::class))->shouldBeCalled();

        $manager->delete($uuid);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreate()
    {
        $manager = new RateBandManager($this->repository->reveal(), $this->partnerRepository->reveal());
        $partner = new Partner();
        $partner->goldenId = '4321';
        $this->partnerRepository->findOneByGoldenId('4321')->willReturn($partner);
        $rateBandCreateRequest = new RateBandCreateRequest();
        $rateBandCreateRequest->goldenId = '1234';
        $rateBandCreateRequest->partnerGoldenId = '4321';
        $rateBandCreateRequest->name = 'testName';

        $this->repository->save(Argument::type(RateBand::class))->shouldBeCalled();

        $rateBand = $manager->create($rateBandCreateRequest);
        $this->assertEquals($rateBandCreateRequest->goldenId, $rateBand->goldenId);
        $this->assertEquals($rateBandCreateRequest->partnerGoldenId, $rateBand->partnerGoldenId);
        $this->assertEquals($rateBandCreateRequest->name, $rateBand->name);
    }
}
