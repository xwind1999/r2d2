<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\BroadcastListener\PriceInformation\Price;
use App\Contract\Request\BroadcastListener\PriceInformation\Product;
use App\Contract\Request\BroadcastListener\PriceInformationRequest;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Internal\Experience\ExperienceCreateRequest;
use App\Contract\Request\Internal\Experience\ExperienceUpdateRequest;
use App\Entity\Experience;
use App\Entity\Partner;
use App\Exception\Manager\Experience\OutdatedExperienceException;
use App\Exception\Manager\Experience\OutdatedExperiencePriceException;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Manager\ExperienceManager;
use App\Repository\ExperienceRepository;
use App\Repository\PartnerRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Manager\ExperienceManager
 */
class ExperienceManagerTest extends TestCase
{
    /**
     * @var ExperienceRepository|ObjectProphecy
     */
    protected $repository;

    /**
     * @var ObjectProphecy|PartnerRepository
     */
    protected $partnerRepository;

    public function setUp(): void
    {
        $this->repository = $this->prophesize(ExperienceRepository::class);
        $this->partnerRepository = $this->prophesize(PartnerRepository::class);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::update
     */
    public function testUpdate()
    {
        $manager = new ExperienceManager($this->repository->reveal(), $this->partnerRepository->reveal());
        $partner = new Partner();
        $partner->goldenId = '5678';
        $this->partnerRepository->findOneByGoldenId('5678')->willReturn($partner);
        $experienceUpdateRequest = new ExperienceUpdateRequest();
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $experienceUpdateRequest->goldenId = '1234';
        $experienceUpdateRequest->partnerGoldenId = '5678';
        $experienceUpdateRequest->name = 'dinner with massage';
        $experienceUpdateRequest->description = 'a fancy dinner with feet massage';
        $experienceUpdateRequest->productPeopleNumber = 1;

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);

        $experience = new Experience();
        $experience->uuid = $uuidInterface->reveal();
        $experience->goldenId = '1234';
        $experience->partnerGoldenId = '5678';
        $experience->name = '7895';
        $experience->description = '12365488';
        $experience->peopleNumber = 2;
        $experience->duration = 3;
        $this->repository->findOne($uuid)->willReturn($experience);

        $this->repository->save(Argument::type(Experience::class))->shouldBeCalled();

        $updatedExperience = $manager->update($uuid, $experienceUpdateRequest);

        $this->assertSame($experience, $updatedExperience);
        $this->assertEquals('5678', $experience->partnerGoldenId);
        $this->assertEquals('dinner with massage', $experience->name);
        $this->assertEquals('a fancy dinner with feet massage', $experience->description);
        $this->assertEquals('1234', $experience->goldenId);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::delete
     */
    public function testDelete()
    {
        $manager = new ExperienceManager($this->repository->reveal(), $this->partnerRepository->reveal());
        $uuid = '12345678';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $experience = new Experience();
        $experience->uuid = $uuidInterface->reveal();
        $this->repository->findOne($uuid)->willReturn($experience);

        $this->repository->delete(Argument::type(Experience::class))->shouldBeCalled();

        $manager->delete($uuid);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreate()
    {
        $manager = new ExperienceManager($this->repository->reveal(), $this->partnerRepository->reveal());
        $partner = new Partner();
        $partner->goldenId = '5678';
        $this->partnerRepository->findOneByGoldenId('5678')->willReturn($partner);
        $experienceCreateRequest = new ExperienceCreateRequest();
        $experienceCreateRequest->goldenId = '5678';
        $experienceCreateRequest->partnerGoldenId = '5678';
        $experienceCreateRequest->name = 'dinner with massage';
        $experienceCreateRequest->description = 'a fancy dinner with feet massage';
        $experienceCreateRequest->productPeopleNumber = 2;

        $this->repository->save(Argument::type(Experience::class))->shouldBeCalled();

        $experience = $manager->create($experienceCreateRequest);
        $this->assertEquals($experienceCreateRequest->goldenId, $experience->goldenId);
        $this->assertEquals($experienceCreateRequest->partnerGoldenId, $experience->partnerGoldenId);
        $this->assertEquals($experienceCreateRequest->name, $experience->name);
        $this->assertEquals($experienceCreateRequest->description, $experience->description);
        $this->assertEquals($experienceCreateRequest->productPeopleNumber, $experience->peopleNumber);
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplace()
    {
        $manager = new ExperienceManager($this->repository->reveal(), $this->partnerRepository->reveal());
        $partner = new \App\Contract\Request\BroadcastListener\Product\Partner();
        $partner->id = '5678';
        $productRequest = new ProductRequest();
        $productRequest->id = '5678';
        $productRequest->partner = $partner;
        $productRequest->name = 'dinner with massage';
        $productRequest->description = 'a fancy dinner with feet massage';
        $productRequest->productPeopleNumber = 2;

        $this->partnerRepository->findOneByGoldenId($productRequest->partner->id);
        $this->repository->findOneByGoldenId($productRequest->id);

        $this->repository->save(Argument::type(Experience::class))->shouldBeCalled();

        $this->assertEmpty($manager->replace($productRequest));
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplaceWithOutdatedRecord()
    {
        $manager = new ExperienceManager($this->repository->reveal(), $this->partnerRepository->reveal());
        $partner = new \App\Contract\Request\BroadcastListener\Product\Partner();
        $partner->id = '5678';
        $productRequest = new ProductRequest();
        $productRequest->id = '5678';
        $productRequest->partner = $partner;
        $productRequest->updatedAt = new \DateTime('2020-01-01 00:00:00');

        $experience = new Experience();
        $experience->externalUpdatedAt = new \DateTime('2020-01-01 01:00:00');

        $this->partnerRepository->findOneByGoldenId($productRequest->partner->id);
        $this->repository->findOneByGoldenId($productRequest->id)->willReturn($experience);

        $this->expectException(OutdatedExperienceException::class);
        $manager->replace($productRequest);
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplaceCatchesExperienceNotFoundException()
    {
        $manager = new ExperienceManager($this->repository->reveal(), $this->partnerRepository->reveal());
        $partner = new \App\Contract\Request\BroadcastListener\Product\Partner();
        $partner->id = '5678';
        $productRequest = new ProductRequest();
        $productRequest->id = '5678';
        $productRequest->partner = $partner;
        $productRequest->name = 'dinner with massage';
        $productRequest->description = 'a fancy dinner with feet massage';
        $productRequest->productPeopleNumber = 2;

        $this->partnerRepository->findOneByGoldenId($productRequest->partner->id);
        $this->repository
            ->findOneByGoldenId($productRequest->id)
            ->shouldBeCalled()
            ->willThrow(new ExperienceNotFoundException())
        ;
        $this->repository->save(Argument::type(Experience::class))->shouldBeCalled();

        $this->assertEmpty($manager->replace($productRequest));
    }

    /**
     * @covers ::__construct
     * @covers ::insertPriceInfo
     */
    public function testinsertPriceInfo()
    {
        $manager = new ExperienceManager($this->repository->reveal(), $this->partnerRepository->reveal());
        $productDTO = new Product();
        $productDTO->id = '1264';
        $priceDTO = new Price();
        $priceDTO->amount = 12;
        $priceInformationRequest = new PriceInformationRequest();
        $priceInformationRequest->product = $productDTO;
        $priceInformationRequest->averageValue = $priceDTO;
        $priceInformationRequest->averageCommission = 5556;
        $priceInformationRequest->averageCommissionType = 'percentage';

        $this->repository
            ->findOneByGoldenId($priceInformationRequest->product->id)
            ->shouldBeCalledOnce()
            ->willReturn(($this->prophesize(Experience::class))->reveal())
        ;
        $this->repository->save(Argument::type(Experience::class))->shouldBeCalledOnce();
        $this->assertEmpty($manager->insertPriceInfo($priceInformationRequest));
    }

    /**
     * @covers ::__construct
     * @covers ::insertPriceInfo
     */
    public function testInsertOutdatedPriceInfo()
    {
        $manager = new ExperienceManager($this->repository->reveal(), $this->partnerRepository->reveal());
        $productDTO = new Product();
        $productDTO->id = '1264';
        $priceInformationRequest = new PriceInformationRequest();
        $priceInformationRequest->product = $productDTO;
        $priceInformationRequest->updatedAt = new \DateTime('2020-01-01 00:00:00');

        $experience = $this->prophesize(Experience::class);
        $experience->priceUpdatedAt = new \DateTime('2020-01-01 01:00:00');

        $this->repository
            ->findOneByGoldenId($priceInformationRequest->product->id)
            ->shouldBeCalledOnce()
            ->willReturn($experience->reveal())
        ;
        $this->expectException(OutdatedExperiencePriceException::class);
        $manager->insertPriceInfo($priceInformationRequest);
    }

    /**
     * @covers ::__construct
     * @covers ::getIdsListWithPartnerChannelManagerInactive
     */
    public function testGetIdsListWithPartnerChannelManagerInactive()
    {
        $expIds = [
            '1234', '4321', '1111',
        ];
        $this->repository->findListExperienceIdsWithInactiveChannelManagerPartner(Argument::any())->willReturn($expIds);
        $manager = new ExperienceManager($this->repository->reveal(), $this->partnerRepository->reveal());
        $manager->getIdsListWithPartnerChannelManagerInactive($expIds);

        $this->repository->findListExperienceIdsWithInactiveChannelManagerPartner($expIds)->shouldBeCalledOnce();
    }

    /**
     * @covers ::__construct
     * @covers ::getOneByGoldenId
     */
    public function testGetOneByGoldenId()
    {
        $experience = new Experience();
        $expId = '1234';
        $experience->goldenId = $expId;
        $this->repository->findOneByGoldenId(Argument::any())->willReturn($experience);
        $manager = new ExperienceManager($this->repository->reveal(), $this->partnerRepository->reveal());
        $manager->getOneByGoldenId($expId);

        $this->repository->findOneByGoldenId($expId)->shouldBeCalledOnce();
    }
}
