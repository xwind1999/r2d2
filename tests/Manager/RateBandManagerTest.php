<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\RateBand\RateBandCreateRequest;
use App\Contract\Request\RateBand\RateBandUpdateRequest;
use App\Entity\RateBand;
use App\Manager\RateBandManager;
use App\Repository\RateBandRepository;
use Doctrine\ORM\EntityManagerInterface;
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
     * @var EntityManagerInterface|ObjectProphecy
     */
    protected $em;

    /**
     * @var ObjectProphecy|RateBandRepository
     */
    protected $repository;

    public function setUp(): void
    {
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->repository = $this->prophesize(RateBandRepository::class);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::update
     */
    public function testUpdate()
    {
        $manager = new RateBandManager($this->em->reveal(), $this->repository->reveal());
        $rateBandUpdateRequest = new RateBandUpdateRequest();
        $uuid = '12345678';
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

        $this->em->persist(Argument::type(RateBand::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $manager->update($rateBandUpdateRequest);

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
        $manager = new RateBandManager($this->em->reveal(), $this->repository->reveal());
        $uuid = '12345678';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $rateBand = new RateBand();
        $rateBand->uuid = $uuidInterface->reveal();
        $this->repository->findOne($uuid)->willReturn($rateBand);

        $this->em->remove(Argument::type(RateBand::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $manager->delete($uuid);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreate()
    {
        $manager = new RateBandManager($this->em->reveal(), $this->repository->reveal());
        $rateBandCreateRequest = new RateBandCreateRequest();
        $rateBandCreateRequest->goldenId = '1234';
        $rateBandCreateRequest->partnerGoldenId = '4321';
        $rateBandCreateRequest->name = 'testName';

        $this->em->persist(Argument::type(RateBand::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $rateBand = $manager->create($rateBandCreateRequest);
        $this->assertEquals($rateBandCreateRequest->goldenId, $rateBand->goldenId);
        $this->assertEquals($rateBandCreateRequest->partnerGoldenId, $rateBand->partnerGoldenId);
        $this->assertEquals($rateBandCreateRequest->name, $rateBand->name);
    }
}
