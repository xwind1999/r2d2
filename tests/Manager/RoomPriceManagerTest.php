<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\BroadcastListener\Common\Price;
use App\Contract\Request\BroadcastListener\Product\Product;
use App\Contract\Request\BroadcastListener\RoomPriceRequest;
use App\Entity\Component;
use App\Entity\RoomPrice;
use App\Exception\Repository\ComponentNotFoundException;
use App\Manager\RoomPriceManager;
use App\Repository\ComponentRepository;
use App\Repository\RoomPriceRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\Manager\RoomPriceManager
 */
class RoomPriceManagerTest extends TestCase
{
    /**
     * @var ObjectProphecy|RoomPriceRepository
     */
    protected $repository;

    /**
     * @var ComponentRepository|ObjectProphecy
     */
    protected $componentRepository;

    /**
     * @var EntityManagerInterface|ObjectProphecy
     */
    protected $em;

    /**
     * @var LoggerInterface|ObjectProphecy
     */
    protected $logger;

    public function setUp(): void
    {
        $this->repository = $this->prophesize(RoomPriceRepository::class);
        $this->componentRepository = $this->prophesize(ComponentRepository::class);
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplace()
    {
        $manager = new RoomPriceManager($this->repository->reveal(), $this->componentRepository->reveal(), $this->em->reveal(), $this->logger->reveal());

        $roomPriceRequest = new RoomPriceRequest();
        $roomPriceRequest->product = new Product();
        $roomPriceRequest->product->id = '1234';
        $roomPriceRequest->dateFrom = new \DateTime('2020-01-01');
        $roomPriceRequest->dateTo = new \DateTime('2020-01-02');
        $roomPriceRequest->price = new Price();
        $roomPriceRequest->price->amount = 123;
        $roomPriceRequest->price->currencyCode = 'EUR';
        $roomPriceRequest->updatedAt = null;

        $roomPriceList = [];
        $roomPrice = new RoomPrice();
        $roomPrice->component = new Component();
        $roomPrice->component->id = '1234';
        $roomPriceList['2020-01-01'] = $roomPrice;

        $component = new Component();
        $this->componentRepository->findOneByGoldenId('1234')->willReturn($component);

        $this->repository->findByComponentAndDateRange(
            $component,
            $roomPriceRequest->dateFrom,
            $roomPriceRequest->dateTo
        )->willReturn($roomPriceList);

        $this->em->flush()->shouldBeCalled();
        $this->em->persist(Argument::type(RoomPrice::class))->shouldBeCalledTimes(2);

        $manager->replace($roomPriceRequest);
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplaceWithOutdatedValue()
    {
        $manager = new RoomPriceManager($this->repository->reveal(), $this->componentRepository->reveal(), $this->em->reveal(), $this->logger->reveal());

        $roomPriceRequest = new RoomPriceRequest();
        $roomPriceRequest->product = new Product();
        $roomPriceRequest->product->id = '1234';
        $roomPriceRequest->dateFrom = new \DateTime('2020-01-01');
        $roomPriceRequest->dateTo = new \DateTime('2020-01-02');
        $roomPriceRequest->price = new Price();
        $roomPriceRequest->price->amount = 123;
        $roomPriceRequest->price->currencyCode = 'EUR';
        $roomPriceRequest->updatedAt = null;

        $roomPriceList = [];
        $roomPrice = new RoomPrice();
        $roomPrice->component = new Component();
        $roomPrice->component->id = '1234';
        $roomPriceList['2020-01-01'] = $roomPrice;
        $roomPriceList['2020-01-02'] = clone $roomPrice;
        $roomPriceList['2020-01-02']->externalUpdatedAt = new \DateTime();

        $component = new Component();

        $this->componentRepository->findOneByGoldenId('1234')->willReturn($component);

        $this->repository->findByComponentAndDateRange(
            $component,
            $roomPriceRequest->dateFrom,
            $roomPriceRequest->dateTo
        )->willReturn($roomPriceList);

        $this->em->flush()->shouldBeCalled();

        $this->em->persist($roomPrice)->shouldBeCalledTimes(1);
        $this->logger->warning('Outdated room price received', $roomPriceRequest->getContext())->shouldBeCalledTimes(1);

        $manager->replace($roomPriceRequest);
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplaceWithNonExistentComponent()
    {
        $manager = new RoomPriceManager($this->repository->reveal(), $this->componentRepository->reveal(), $this->em->reveal(), $this->logger->reveal());

        $roomPriceRequest = new RoomPriceRequest();
        $roomPriceRequest->product = new Product();
        $roomPriceRequest->product->id = '1234';

        $this->componentRepository->findOneByGoldenId('1234')->willThrow(new ComponentNotFoundException());

        $this->expectException(ComponentNotFoundException::class);

        $manager->replace($roomPriceRequest);
    }
}
