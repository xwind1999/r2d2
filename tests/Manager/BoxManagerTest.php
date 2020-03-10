<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\Box\BoxCreateRequest;
use App\Contract\Request\Box\BoxUpdateRequest;
use App\Entity\Box;
use App\Manager\BoxManager;
use App\Repository\BoxRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Manager\BoxManager
 */
class BoxManagerTest extends TestCase
{
    /**
     * @var EntityManagerInterface|ObjectProphecy
     */
    protected $em;

    /**
     * @var BoxRepository|ObjectProphecy
     */
    protected $repository;

    public function setUp(): void
    {
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->repository = $this->prophesize(BoxRepository::class);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::update
     */
    public function testUpdate()
    {
        $manager = new BoxManager($this->em->reveal(), $this->repository->reveal());
        $boxUpdateRequest = new BoxUpdateRequest();
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $boxUpdateRequest->goldenId = '5678';
        $boxUpdateRequest->brand = 'sbx';
        $boxUpdateRequest->country = 'fr';
        $boxUpdateRequest->status = 'integrated2';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);

        $box = new Box();
        $box->uuid = $uuidInterface->reveal();
        $box->goldenId = '1234';
        $box->brand = 'bon';
        $box->country = 'be';
        $box->status = 'integrated';
        $this->repository->findOne($uuid)->willReturn($box);

        $this->em->persist(Argument::type(Box::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $manager->update($uuid, $boxUpdateRequest);

        $this->assertEquals('integrated2', $box->status);
        $this->assertEquals('sbx', $box->brand);
        $this->assertEquals('fr', $box->country);
        $this->assertEquals('5678', $box->goldenId);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::delete
     */
    public function testDelete()
    {
        $manager = new BoxManager($this->em->reveal(), $this->repository->reveal());
        $uuid = '12345678';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $box = new Box();
        $box->uuid = $uuidInterface->reveal();
        $this->repository->findOne($uuid)->willReturn($box);

        $this->em->remove(Argument::type(Box::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $manager->delete($uuid);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreate()
    {
        $manager = new BoxManager($this->em->reveal(), $this->repository->reveal());
        $boxCreateRequest = new BoxCreateRequest();
        $boxCreateRequest->goldenId = '5678';
        $boxCreateRequest->brand = 'sbx';
        $boxCreateRequest->country = 'fr';
        $boxCreateRequest->status = 'integrated2';

        $this->em->persist(Argument::type(Box::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $box = $manager->create($boxCreateRequest);
        $this->assertEquals($boxCreateRequest->goldenId, $box->goldenId);
        $this->assertEquals($boxCreateRequest->brand, $box->brand);
        $this->assertEquals($boxCreateRequest->country, $box->country);
        $this->assertEquals($boxCreateRequest->status, $box->status);
    }
}
