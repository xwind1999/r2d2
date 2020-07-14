<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\BroadcastListener\PriceInformation\Price;
use App\Contract\Request\BroadcastListener\PriceInformation\Product;
use App\Contract\Request\BroadcastListener\PriceInformationRequest;
use App\Contract\Request\BroadcastListener\Product\Brand;
use App\Contract\Request\BroadcastListener\Product\Country;
use App\Contract\Request\BroadcastListener\Product\ListPrice;
use App\Contract\Request\BroadcastListener\Product\Universe;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Internal\Box\BoxCreateRequest;
use App\Contract\Request\Internal\Box\BoxUpdateRequest;
use App\Entity\Box;
use App\Exception\Manager\Box\OutdatedBoxException;
use App\Exception\Repository\BoxNotFoundException;
use App\Helper\Manageable\ManageableProductService;
use App\Manager\BoxManager;
use App\Repository\BoxRepository;
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
     * @var BoxRepository|ObjectProphecy
     */
    protected $repository;

    /**
     * @var ManageableProductService|ObjectProphecy
     */
    private $manageableProductService;

    public function setUp(): void
    {
        $this->repository = $this->prophesize(BoxRepository::class);
        $this->manageableProductService = $this->prophesize(ManageableProductService::class);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::update
     */
    public function testUpdate(): void
    {
        $manager = new BoxManager(
            $this->repository->reveal(),
            $this->manageableProductService->reveal()
        );
        $boxUpdateRequest = $this->prophesize(BoxUpdateRequest::class);
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $boxUpdateRequest->goldenId = '5678';
        $boxUpdateRequest->brand = 'sbx';
        $boxUpdateRequest->country = 'fr';
        $boxUpdateRequest->status = 'integrated2';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);

        $box = $this->prophesize(Box::class);
        $box->uuid = $uuidInterface->reveal();
        $box->goldenId = '1234';
        $box->brand = 'bon';
        $box->country = 'be';
        $box->status = 'integrated';
        $this->repository->findOne($uuid)->willReturn($box->reveal());
        $this->repository->save(Argument::type(Box::class))->shouldBeCalled();

        $manager->update($uuid, $boxUpdateRequest->reveal());

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
    public function testDelete(): void
    {
        $manager = new BoxManager(
            $this->repository->reveal(),
            $this->manageableProductService->reveal()
        );
        $uuid = '12345678';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $box = new Box();
        $box->uuid = $uuidInterface->reveal();
        $this->repository->findOne($uuid)->willReturn($box);
        $this->repository->delete(Argument::type(Box::class))->shouldBeCalled();

        $manager->delete($uuid);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreate(): void
    {
        $manager = new BoxManager(
            $this->repository->reveal(),
            $this->manageableProductService->reveal()
        );
        $boxCreateRequest = $this->prophesize(BoxCreateRequest::class);
        $boxCreateRequest->goldenId = '5678';
        $boxCreateRequest->brand = 'sbx';
        $boxCreateRequest->country = 'fr';
        $boxCreateRequest->status = 'integrated2';

        $this->repository->save(Argument::type(Box::class))->shouldBeCalled();
        $box = $manager->create($boxCreateRequest->reveal());

        $this->assertEquals($boxCreateRequest->goldenId, $box->goldenId);
        $this->assertEquals($boxCreateRequest->brand, $box->brand);
        $this->assertEquals($boxCreateRequest->country, $box->country);
        $this->assertEquals($boxCreateRequest->status, $box->status);
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplace(): void
    {
        $manager = new BoxManager(
            $this->repository->reveal(),
            $this->manageableProductService->reveal()
        );
        $brand = Brand::create('SBX');
        $country = Country::create('FR');
        $productRequest = $this->prophesize(ProductRequest::class);
        $productRequest->id = '1234';
        $productRequest->sellableBrand = $brand;
        $productRequest->sellableCountry = $country;
        $productRequest->status = 'active';
        $productRequest->listPrice = $this->prophesize(ListPrice::class);
        $productRequest->listPrice->currencyCode = 'EUR';
        $universe = new Universe();
        $universe->id = 'STA';
        $productRequest->universe = $universe;
        $box = $this->prophesize(Box::class);
        $box->status = 'inactive';

        $this->repository->findOneByGoldenId($productRequest->id)->willReturn($box->reveal());
        $this->repository->save(Argument::type(Box::class))->shouldBeCalled();
        $this->manageableProductService->dispatchForBox(Argument::any(), Argument::any())->shouldBeCalled();

        $manager->replace($productRequest->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplaceWithOutdatedRecord(): void
    {
        $manager = new BoxManager(
            $this->repository->reveal(),
            $this->manageableProductService->reveal()
        );
        $productRequest = $this->prophesize(ProductRequest::class);
        $productRequest->id = '1234';
        $productRequest->updatedAt = new \DateTime('2020-01-01 00:00:00');

        $box = $this->prophesize(Box::class);
        $box->externalUpdatedAt = new \DateTime('2020-01-01 01:00:00');
        $box->status = 'inactive';

        $this->repository->findOneByGoldenId($productRequest->id)->willReturn($box->reveal());

        $this->expectException(OutdatedBoxException::class);
        $manager->replace($productRequest->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplaceCatchesBoxNotFoundException(): void
    {
        $manager = new BoxManager(
            $this->repository->reveal(),
            $this->manageableProductService->reveal()
        );
        $brand = Brand::create('SBX');
        $country = Country::create('FR');
        $productRequest = $this->prophesize(ProductRequest::class);
        $productRequest->id = '1234';
        $productRequest->sellableBrand = $brand;
        $productRequest->sellableCountry = $country;
        $productRequest->status = 'active';
        $productRequest->listPrice = new ListPrice();
        $productRequest->listPrice->currencyCode = 'EUR';

        $this->repository
            ->findOneByGoldenId($productRequest->id)
            ->shouldBeCalled()
            ->willThrow(new BoxNotFoundException())
        ;
        $this->manageableProductService->dispatchForBox(Argument::any(), Argument::any())->shouldBeCalled();
        $this->repository->save(Argument::type(Box::class))->shouldBeCalled();
        $manager->replace($productRequest->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::insertPriceInfo
     */
    public function testinsertPriceInfo(): void
    {
        $manager = new BoxManager(
            $this->repository->reveal(),
            $this->manageableProductService->reveal()
        );
        $productDTO = $this->prophesize(Product::class);
        $productDTO->id = '1264';
        $priceDTO = $this->prophesize(Price::class);
        $priceDTO->amount = 12;
        $priceDTO->currencyCode = 'EUR';
        $priceInformationRequest = $this->prophesize(PriceInformationRequest::class);
        $priceInformationRequest->product = $productDTO;
        $priceInformationRequest->averageValue = $priceDTO;
        $priceInformationRequest->averageCommission = '55.56';
        $priceInformationRequest->averageCommissionType = 'amount';

        $this->repository
            ->findOneByGoldenId($priceInformationRequest->product->id)
            ->shouldBeCalledOnce()
            ->willReturn(($this->prophesize(Box::class))->reveal())
        ;
        $this->repository->save(Argument::type(Box::class))->shouldBeCalledOnce();
        $manager->insertPriceInfo($priceInformationRequest->reveal());
    }
}
