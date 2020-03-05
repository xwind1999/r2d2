<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\Partner\PartnerCreateRequest;
use App\Contract\Request\Partner\PartnerUpdateRequest;
use App\Entity\Partner;
use App\Manager\PartnerManager;
use App\Repository\PartnerRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Manager\PartnerManager
 */
class PartnerManagerTest extends TestCase
{
    /**
     * @var EntityManagerInterface|ObjectProphecy
     */
    protected $em;

    /**
     * @var ObjectProphecy|PartnerRepository
     */
    protected $repository;

    public function setUp(): void
    {
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->repository = $this->prophesize(PartnerRepository::class);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::update
     */
    public function testUpdate()
    {
        $manager = new PartnerManager($this->em->reveal(), $this->repository->reveal());
        $partnerUpdateRequest = new PartnerUpdateRequest();
        $uuid = '12345678';
        $partnerUpdateRequest->uuid = $uuid;
        $partnerUpdateRequest->goldenId = '1234';
        $partnerUpdateRequest->status = 'alive';
        $partnerUpdateRequest->currency = 'USD';
        $partnerUpdateRequest->ceaseDate = new \DateTime('2020-10-10');

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);

        $partner = new Partner();
        $partner->uuid = $uuidInterface->reveal();
        $partner->goldenId = '1234';
        $partner->status = 'alive';
        $partner->currency = 'USD';
        $partner->ceaseDate = new \DateTime('2020-10-10');
        $this->repository->findOne($uuid)->willReturn($partner);

        $this->em->persist(Argument::type(Partner::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $manager->update($partnerUpdateRequest);

        $this->assertEquals('1234', $partner->goldenId);
        $this->assertEquals('alive', $partner->status);
        $this->assertEquals('USD', $partner->currency);
        $this->assertEquals(new \DateTime('2020-10-10'), $partner->ceaseDate);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::delete
     */
    public function testDelete()
    {
        $manager = new PartnerManager($this->em->reveal(), $this->repository->reveal());
        $uuid = '12345678';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $partner = new Partner();
        $partner->uuid = $uuidInterface->reveal();
        $this->repository->findOne($uuid)->willReturn($partner);

        $this->em->remove(Argument::type(Partner::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $manager->delete($uuid);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreate()
    {
        $manager = new PartnerManager($this->em->reveal(), $this->repository->reveal());
        $partnerCreateRequest = new PartnerCreateRequest();
        $partnerCreateRequest->goldenId = '1234';
        $partnerCreateRequest->status = 'alive';
        $partnerCreateRequest->currency = 'USD';
        $partnerCreateRequest->ceaseDate = new \DateTime('2020-10-10');

        $this->em->persist(Argument::type(Partner::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $partner = $manager->create($partnerCreateRequest);
        $this->assertEquals($partnerCreateRequest->goldenId, $partner->goldenId);
        $this->assertEquals($partnerCreateRequest->status, $partner->status);
        $this->assertEquals($partnerCreateRequest->currency, $partner->currency);
        $this->assertEquals($partnerCreateRequest->ceaseDate, $partner->ceaseDate);
    }
}
