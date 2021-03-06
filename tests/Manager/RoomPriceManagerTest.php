<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\BroadcastListener\Common\Price;
use App\Contract\Request\BroadcastListener\Product\Product;
use App\Contract\Request\BroadcastListener\RoomPriceRequest;
use App\Contract\Request\BroadcastListener\RoomPriceRequestList;
use App\Entity\Component;
use App\Entity\RoomPrice;
use App\Exception\Repository\ComponentNotFoundException;
use App\Manager\RoomPriceManager;
use App\Repository\ComponentRepository;
use App\Repository\RoomPriceRepository;
use App\Tests\ProphecyTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \App\Manager\RoomPriceManager
 */
class RoomPriceManagerTest extends ProphecyTestCase
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

    /**
     * @var MessageBusInterface|ObjectProphecy
     */
    private $messageBus;

    private RoomPriceManager $manager;

    public function setUp(): void
    {
        $this->repository = $this->prophesize(RoomPriceRepository::class);
        $this->componentRepository = $this->prophesize(ComponentRepository::class);
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->manager = new RoomPriceManager(
            $this->repository->reveal(),
            $this->componentRepository->reveal(),
            $this->em->reveal(),
            $this->messageBus->reveal(),
            $this->logger->reveal()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplace()
    {
        $roomPriceRequest = new RoomPriceRequest();
        $roomPriceRequest->product = new Product();
        $roomPriceRequest->product->id = '1234';
        $roomPriceRequest->dateFrom = new \DateTime('2020-01-01');
        $roomPriceRequest->dateTo = new \DateTime('2020-01-03');
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

        $this->manager->replace($roomPriceRequest);
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplaceWithOutdatedValue()
    {
        $roomPriceRequest = new RoomPriceRequest();
        $roomPriceRequest->product = new Product();
        $roomPriceRequest->product->id = '1234';
        $roomPriceRequest->dateFrom = new \DateTime('2020-01-01');
        $roomPriceRequest->dateTo = new \DateTime('2020-01-02');
        $roomPriceRequest->price = new Price();
        $roomPriceRequest->price->amount = 123;
        $roomPriceRequest->price->currencyCode = 'EUR';
        $roomPriceRequest->updatedAt = new \DateTime('2019-12-01');

        $roomPriceList = [];
        $roomPrice = new RoomPrice();
        $roomPrice->component = new Component();
        $roomPrice->component->id = '1234';
        $roomPriceList['2020-01-01'] = $roomPrice;
        $roomPriceList['2020-01-01']->externalUpdatedAt = new \DateTime('now');
        $roomPriceList['2020-01-02'] = clone $roomPrice;
        $roomPriceList['2020-01-02']->externalUpdatedAt = new \DateTime('now');

        $component = new Component();

        $this->componentRepository->findOneByGoldenId('1234')->willReturn($component);

        $this->repository->findByComponentAndDateRange(
            $component,
            $roomPriceRequest->dateFrom,
            $roomPriceRequest->dateTo
        )->willReturn($roomPriceList);

        $this->em->flush()->shouldBeCalled();

        $this->em->persist($roomPrice)->shouldNotBeCalled();
        $this->logger->warning('Outdated room price received', $roomPriceRequest->getContext())->shouldBeCalledTimes(1);

        $this->manager->replace($roomPriceRequest);
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplaceWithNonExistentComponent()
    {
        $roomPriceRequest = new RoomPriceRequest();
        $roomPriceRequest->product = new Product();
        $roomPriceRequest->product->id = '1234';

        $this->componentRepository->findOneByGoldenId('1234')->willThrow(new ComponentNotFoundException());

        $this->expectException(ComponentNotFoundException::class);

        $this->manager->replace($roomPriceRequest);
    }

    /**
     * @covers ::dispatchRoomPricesRequest
     */
    public function testDispatchRoomPricesRequest()
    {
        $product = new Product();
        $product->id = '299994';
        $roomPriceRequestList = new RoomPriceRequestList();
        $roomPriceRequest = new RoomPriceRequest();

        $roomPriceRequest->product = $product;
        $roomPriceRequest->price = new Price();
        $roomPriceRequest->price->amount = 123;
        $roomPriceRequest->price->currencyCode = 'EUR';
        $roomPriceRequest->dateFrom = new \DateTime('+5 days');
        $roomPriceRequest->dateTo = new \DateTime('+8 days');
        $roomPriceRequest->updatedAt = new \DateTime('now');

        $roomPriceRequest2 = (clone $roomPriceRequest);
        $roomPriceRequest2->product = clone $product;
        $roomPriceRequest2->product->id = '218439';

        $roomPriceRequest3 = (clone $roomPriceRequest);
        $roomPriceRequest3->product = clone $product;
        $roomPriceRequest3->product->id = '315172';

        $roomPriceRequestList->items = [
            $roomPriceRequest,
            $roomPriceRequest2,
            $roomPriceRequest3,
        ];

        $this->componentRepository->filterManageableComponetsByComponentId(['299994', '218439', '315172'])->willReturn(['299994' => [], '218439' => []]);

        $this
            ->messageBus
            ->dispatch(Argument::is($roomPriceRequest))->willReturn(new Envelope(new \stdClass()))
            ->shouldBeCalled();
        $this
            ->messageBus
            ->dispatch(Argument::is($roomPriceRequest2))->willReturn(new Envelope(new \stdClass()))
            ->shouldBeCalled();

        $this
            ->logger
            ->warning('Received room price for unknown component', $roomPriceRequest3->getContext())
            ->shouldBeCalled();

        $this->manager->dispatchRoomPricesRequest($roomPriceRequestList);
    }
}
