<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Constraint\PartnerStatusConstraint;
use App\Contract\Request\BroadcastListener\PartnerRequest;
use App\Contract\Request\Internal\Partner\PartnerCreateRequest;
use App\Contract\Request\Internal\Partner\PartnerUpdateRequest;
use App\Entity\Partner;
use App\Exception\Manager\Partner\OutdatedPartnerException;
use App\Exception\Repository\PartnerNotFoundException;
use App\Helper\Manageable\ManageableProductService;
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

    /**
     * @var ManageableProductService|ObjectProphecy
     */
    private $manageableProductService;

    public function setUp(): void
    {
        $this->repository = $this->prophesize(PartnerRepository::class);
        $this->manageableProductService = $this->prophesize(ManageableProductService::class);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::update
     */
    public function testUpdate()
    {
        $manager = new PartnerManager($this->repository->reveal(), $this->manageableProductService->reveal());
        $partnerUpdateRequest = new PartnerUpdateRequest();
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $partnerUpdateRequest->goldenId = '1234';
        $partnerUpdateRequest->status = 'alive';
        $partnerUpdateRequest->currency = 'USD';
        $partnerUpdateRequest->isChannelManagerActive = true;
        $partnerUpdateRequest->ceaseDate = new \DateTime('2015-10-12T23:03:09.000000+0000');

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);

        $partner = new Partner();
        $partner->uuid = $uuidInterface->reveal();
        $partner->goldenId = '1234';
        $partner->status = 'alive';
        $partner->currency = 'USD';
        $partner->isChannelManagerActive = true;
        $partner->ceaseDate = new \DateTime('2015-10-12T23:03:09.000000+0000');
        $this->repository->findOne($uuid)->willReturn($partner);

        $this->repository->save(Argument::type(Partner::class))->shouldBeCalled();

        $updatedPartner = $manager->update($uuid, $partnerUpdateRequest);

        $this->assertSame($partner, $updatedPartner);
        $this->assertEquals('1234', $partner->goldenId);
        $this->assertEquals('alive', $partner->status);
        $this->assertEquals('USD', $partner->currency);
        $this->assertEquals(new \DateTime('2015-10-12T23:03:09.000000+0000'), $partner->ceaseDate);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::delete
     */
    public function testDelete()
    {
        $manager = new PartnerManager($this->repository->reveal(), $this->manageableProductService->reveal());
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
        $manager = new PartnerManager($this->repository->reveal(), $this->manageableProductService->reveal());
        $partnerCreateRequest = new PartnerCreateRequest();
        $partnerCreateRequest->goldenId = '1234';
        $partnerCreateRequest->status = 'alive';
        $partnerCreateRequest->currency = 'USD';
        $partnerCreateRequest->isChannelManagerActive = true;
        $partnerCreateRequest->ceaseDate = new \DateTime('2015-10-12T23:03:09.000000+0000');

        $this->repository->save(Argument::type(Partner::class))->shouldBeCalled();

        $partner = $manager->create($partnerCreateRequest);
        $this->assertEquals($partnerCreateRequest->goldenId, $partner->goldenId);
        $this->assertEquals($partnerCreateRequest->status, $partner->status);
        $this->assertEquals($partnerCreateRequest->currency, $partner->currency);
        $this->assertEquals($partnerCreateRequest->ceaseDate, $partner->ceaseDate);
    }

    /**
     * @covers ::__construct
     * @covers ::createPlaceholder
     */
    public function testCreatePlaceholder()
    {
        $manager = new PartnerManager($this->repository->reveal(), $this->manageableProductService->reveal());
        $partner = $manager->createPlaceholder('1234');

        $this->repository->save(Argument::type(Partner::class))->shouldBeCalled();

        $this->assertEquals('1234', $partner->goldenId);
        $this->assertEquals(PartnerStatusConstraint::PARTNER_STATUS_PLACEHOLDER, $partner->status);
        $this->assertNull($partner->externalUpdatedAt);
        $this->assertEquals('', $partner->currency);
        $this->assertFalse($partner->isChannelManagerActive);
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplace()
    {
        $manager = new PartnerManager($this->repository->reveal(), $this->manageableProductService->reveal());
        $partnerRequest = new PartnerRequest();
        $partnerRequest->id = '1234';
        $partnerRequest->status = 'active';
        $partnerRequest->currencyCode = 'EUR';
        $partnerRequest->isChannelManagerEnabled = true;
        $partnerRequest->partnerCeaseDate = new \DateTime('2015-10-12T23:03:09.000000+0000');

        $this->repository->findOneByGoldenId($partnerRequest->id)->shouldBeCalled();
        $this->repository->save(Argument::type(Partner::class))->shouldBeCalled();
        $this->manageableProductService->dispatchForPartner($partnerRequest, Argument::type(Partner::class))->shouldBeCalled();

        $this->assertEmpty($manager->replace($partnerRequest));
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplaceWithOutdatedRecord()
    {
        $manager = new PartnerManager($this->repository->reveal(), $this->manageableProductService->reveal());
        $partnerRequest = new PartnerRequest();
        $partnerRequest->id = '1234';
        $partnerRequest->updatedAt = new \DateTime('2020-01-01 00:00:00');

        $partner = new Partner();
        $partner->externalUpdatedAt = new \DateTime('2020-01-01 01:00:00');
        $this->repository->findOneByGoldenId($partnerRequest->id)->willReturn($partner);
        $this->expectException(OutdatedPartnerException::class);
        $manager->replace($partnerRequest);
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplaceCatchesPartnerNotFoundException()
    {
        $manager = new PartnerManager($this->repository->reveal(), $this->manageableProductService->reveal());
        $partnerRequest = new PartnerRequest();
        $partnerRequest->id = '1584878545';
        $partnerRequest->status = 'active';
        $partnerRequest->currencyCode = 'USD';
        $partnerRequest->isChannelManagerEnabled = true;
        $partnerRequest->partnerCeaseDate = new \DateTime('2015-10-12T23:03:09.000000+0000');

        $this->repository
            ->findOneByGoldenId($partnerRequest->id)
            ->shouldBeCalled()
            ->willThrow(new PartnerNotFoundException())
        ;
        $this->repository->save(Argument::type(Partner::class))->shouldBeCalled();

        $this->assertEmpty($manager->replace($partnerRequest));
    }

    /**
     * @covers ::__construct
     * @covers ::getOneByGoldenId
     */
    public function testGetOneByGoldenId()
    {
        $partner = new Partner();
        $partnerGoldenId = '1234';
        $partner->goldenId = $partnerGoldenId;
        $this->repository->findOneByGoldenId(Argument::any())->willReturn($partner);
        $manager = new PartnerManager($this->repository->reveal(), $this->manageableProductService->reveal());
        $manager->getOneByGoldenId($partnerGoldenId);

        $this->repository->findOneByGoldenId($partnerGoldenId)->shouldBeCalledOnce();
    }
}
