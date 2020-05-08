<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\BroadcastListener\PartnerRequest;
use App\Contract\Request\Internal\Partner\PartnerCreateRequest;
use App\Contract\Request\Internal\Partner\PartnerUpdateRequest;
use App\Entity\Partner;
use App\Exception\Repository\PartnerNotFoundException;
use App\Manager\PartnerManager;
use App\Repository\PartnerRepository;
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
     * @var ObjectProphecy|PartnerRepository
     */
    protected $repository;

    public function setUp(): void
    {
        $this->repository = $this->prophesize(PartnerRepository::class);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::update
     */
    public function testUpdate()
    {
        $manager = new PartnerManager($this->repository->reveal());
        $partnerUpdateRequest = new PartnerUpdateRequest();
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $partnerUpdateRequest->goldenId = '1234';
        $partnerUpdateRequest->status = 'alive';
        $partnerUpdateRequest->currency = 'USD';
        $partnerUpdateRequest->isChannelManagerActive = true;
        $partnerUpdateRequest->ceaseDate = new \DateTime('2020-10-10');

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);

        $partner = new Partner();
        $partner->uuid = $uuidInterface->reveal();
        $partner->goldenId = '1234';
        $partner->status = 'alive';
        $partner->currency = 'USD';
        $partner->isChannelManagerActive = true;
        $partner->ceaseDate = new \DateTime('2020-10-10');
        $this->repository->findOne($uuid)->willReturn($partner);

        $this->repository->save(Argument::type(Partner::class))->shouldBeCalled();

        $updatedPartner = $manager->update($uuid, $partnerUpdateRequest);

        $this->assertSame($partner, $updatedPartner);
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
        $manager = new PartnerManager($this->repository->reveal());
        $uuid = '12345678';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $partner = new Partner();
        $partner->uuid = $uuidInterface->reveal();
        $this->repository->findOne($uuid)->willReturn($partner);

        $this->repository->delete(Argument::type(Partner::class))->shouldBeCalled();

        $manager->delete($uuid);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreate()
    {
        $manager = new PartnerManager($this->repository->reveal());
        $partnerCreateRequest = new PartnerCreateRequest();
        $partnerCreateRequest->goldenId = '1234';
        $partnerCreateRequest->status = 'alive';
        $partnerCreateRequest->currency = 'USD';
        $partnerCreateRequest->isChannelManagerActive = true;
        $partnerCreateRequest->ceaseDate = new \DateTime('2020-10-10');

        $this->repository->save(Argument::type(Partner::class))->shouldBeCalled();

        $partner = $manager->create($partnerCreateRequest);
        $this->assertEquals($partnerCreateRequest->goldenId, $partner->goldenId);
        $this->assertEquals($partnerCreateRequest->status, $partner->status);
        $this->assertEquals($partnerCreateRequest->currency, $partner->currency);
        $this->assertEquals($partnerCreateRequest->ceaseDate, $partner->ceaseDate);
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplace()
    {
        $manager = new PartnerManager($this->repository->reveal());
        $partnerRequest = new PartnerRequest();
        $partnerRequest->id = '1234';
        $partnerRequest->status = 'active';
        $partnerRequest->currencyCode = 'EUR';
        $partnerRequest->isChannelManagerEnabled = true;
        $partnerRequest->partnerCeaseDate = new \DateTime('2020-10-10');

        $this->repository->findOneByGoldenId($partnerRequest->id)->shouldBeCalled();
        $this->repository->save(Argument::type(Partner::class))->shouldBeCalled();

        $this->assertEmpty($manager->replace($partnerRequest));
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplaceCatchesPartnerNotFoundException()
    {
        $manager = new PartnerManager($this->repository->reveal());
        $partnerRequest = new PartnerRequest();
        $partnerRequest->id = '1584878545';
        $partnerRequest->status = 'active';
        $partnerRequest->currencyCode = 'USD';
        $partnerRequest->isChannelManagerEnabled = true;
        $partnerRequest->partnerCeaseDate = new \DateTime('2020-10-10');

        $this->repository
            ->findOneByGoldenId($partnerRequest->id)
            ->shouldBeCalled()
            ->willThrow(new PartnerNotFoundException())
        ;
        $this->repository->save(Argument::type(Partner::class))->shouldBeCalled();

        $this->assertEmpty($manager->replace($partnerRequest));
    }
}
