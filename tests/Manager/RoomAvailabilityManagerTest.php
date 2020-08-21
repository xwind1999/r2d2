<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\BroadcastListener\Product\Product;
use App\Contract\Request\BroadcastListener\RoomAvailabilityRequest;
use App\Entity\Component;
use App\Entity\RoomAvailability;
use App\Exception\Manager\RoomAvailability\InvalidRoomStockTypeException;
use App\Exception\Manager\RoomAvailability\OutdatedRoomAvailabilityInformationException;
use App\Exception\Repository\ComponentNotFoundException;
use App\Manager\RoomAvailabilityManager;
use App\Repository\ComponentRepository;
use App\Repository\RoomAvailabilityRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

/**
 * @coversDefaultClass \App\Manager\RoomAvailabilityManager
 */
class RoomAvailabilityManagerTest extends TestCase
{
    /**
     * @var ObjectProphecy|RoomAvailabilityRepository
     */
    protected $repository;

    /**
     * @var ComponentRepository|ObjectProphecy
     */
    protected $componentRepository;

    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private ObjectProphecy $logger;

    public function setUp(): void
    {
        $this->repository = $this->prophesize(RoomAvailabilityRepository::class);
        $this->componentRepository = $this->prophesize(ComponentRepository::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAvailabilitiesByMultipleComponentGoldenIds
     */
    public function testGetRoomAvailabilitiesByComponentGoldenIds()
    {
        $compIds = [
            '1234', '4321', '1111',
        ];
        $this->repository->findRoomAvailabilitiesByMultipleComponentGoldenIds(Argument::any(), Argument::any(), Argument::any())->willReturn($compIds);
        $manager = new RoomAvailabilityManager($this->repository->reveal(), $this->componentRepository->reveal(), $this->logger->reveal());
        $manager->getRoomAvailabilitiesByMultipleComponentGoldenIds($compIds, new \DateTime('2020-06-20'), new \DateTime('2020-06-30'));

        $this->repository->findRoomAvailabilitiesByMultipleComponentGoldenIds($compIds, new \DateTime('2020-06-20'), new \DateTime('2020-06-30'))->shouldBeCalledOnce();
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAvailabilitiesByComponentGoldenId
     */
    public function testGetRoomAvailabilitiesListByComponentGoldenId()
    {
        $this->repository->findRoomAvailabilitiesByComponentGoldenId(Argument::any(), Argument::any(), Argument::any())
            ->willReturn(
                [
                    0 => [
                        'stock' => 10,
                        'date' => '2020-07-20',
                        'type' => 'instant',
                    ],
                ]
            );
        $manager = new RoomAvailabilityManager($this->repository->reveal(), $this->componentRepository->reveal(), $this->logger->reveal());
        $manager->getRoomAvailabilitiesByComponentGoldenId('1234', new \DateTime('2020-06-20'), new \DateTime('2020-06-30'));

        $this->repository->findRoomAvailabilitiesByComponentGoldenId('1234', new \DateTime('2020-06-20'), new \DateTime('2020-06-30'))
            ->shouldBeCalledOnce();
    }

    /**
     * @dataProvider roomAvailabilityRequestProvider
     * @covers ::replace
     * @covers ::createDatePeriod
     * @group replace-room-availability
     *
     * @throws \Exception
     */
    public function testReplace(
        Component $component,
        callable $stubs,
        RoomAvailabilityRequest $roomAvailabilityRequest,
        array $roomAvailabilityList = null,
        ?string $exceptionClass = null
    ) {
        $stubs($this, $roomAvailabilityList, $component, $roomAvailabilityRequest, $this->logger);

        if ($exceptionClass) {
            $this->expectException($exceptionClass);
        }

        $manager = new RoomAvailabilityManager(
            $this->repository->reveal(),
            $this->componentRepository->reveal(),
            $this->logger->reveal()
        );

        $this->assertNull($manager->replace($roomAvailabilityRequest));
    }

    public function roomAvailabilityRequestProvider()
    {
        $component = new Component();
        $component->goldenId = '218439';
        $component->roomStockType = 'on_request';

        $roomAvailabilityRequest = new RoomAvailabilityRequest();
        $roomAvailabilityRequest->product = new Product();
        $roomAvailabilityRequest->product->id = $component->goldenId;
        $roomAvailabilityRequest->quantity = 5;
        $roomAvailabilityRequest->dateFrom = new \DateTime('2019-03-20T20:00:00.000000+0000');
        $roomAvailabilityRequest->dateTo = (clone $roomAvailabilityRequest->dateFrom)->modify('+3 days');
        $roomAvailabilityRequest->updatedAt = new \DateTime('now');

        $roomAvailabilityExistent = new RoomAvailability();
        $roomAvailabilityExistent->uuid = Uuid::uuid4();
        $roomAvailabilityExistent->component = $component;
        $roomAvailabilityExistent->componentGoldenId = $component->goldenId;
        $roomAvailabilityExistent->type = $component->roomStockType ?? '';
        $roomAvailabilityExistent->date = $roomAvailabilityRequest->dateFrom;
        $roomAvailabilityExistent->stock = 2;
        $roomAvailabilityExistent->externalUpdatedAt = new \DateTime('-2 days');

        $date = $roomAvailabilityRequest->dateFrom;

        $date2 = (clone $date)->modify('+1 day');
        $roomAvailabilityExistent2 = clone $roomAvailabilityExistent;
        $roomAvailabilityExistent2->date = $date2;

        $date3 = (clone $date)->modify('+2 days');
        $roomAvailabilityExistent3 = clone $roomAvailabilityExistent;
        $roomAvailabilityExistent3->date = $date3;

        $date4 = (clone $date)->modify('+3 days');
        $roomAvailabilityExistent4 = clone $roomAvailabilityExistent;
        $roomAvailabilityExistent4->date = $date4;

        $roomAvailabilityList = [
            $date->format('Y-m-d') => $roomAvailabilityExistent,
            $date2->format('Y-m-d') => $roomAvailabilityExistent2,
            $date3->format('Y-m-d') => $roomAvailabilityExistent3,
            $date4->format('Y-m-d') => $roomAvailabilityExistent4,
        ];

        yield 'room-availability-update-request' => [
            $component,
            (function ($test, $roomAvailabilityList, $component) {
                $test->repository->findByComponentAndDateRange(Argument::cetera())->willReturn($roomAvailabilityList);
                $test->repository->save(Argument::cetera())->shouldBeCalled();
                $test->componentRepository->findOneByGoldenId(Argument::any())->willReturn($component);
            }),
            (function ($roomAvailabilityRequest) {
                $roomAvailabilityRequest->quantity = random_int(0, 9) < 2 ? 0 : 1;
                $roomAvailabilityRequest->updatedAt->modify('now');

                return $roomAvailabilityRequest;
            })(clone $roomAvailabilityRequest),
            (function ($roomAvailabilityList) {
                foreach ($roomAvailabilityList as $roomAvailability) {
                    $roomAvailability->externalUpdatedAt->modify('-1 week');
                }

                return $roomAvailabilityList;
            })($roomAvailabilityList),
        ];

        yield 'room-availability-already-updated' => [
            $component,
            (function ($test, $roomAvailabilityList, $component) {
                $test->repository->findByComponentAndDateRange(Argument::cetera())->willReturn($roomAvailabilityList);
                $test->repository->save(Argument::cetera())->shouldBeCalled();
                $test->componentRepository->findOneByGoldenId(Argument::any())->willReturn($component);
            }),
            (function ($roomAvailabilityRequest, $roomAvailabilityExistent) {
                $roomAvailabilityRequest = clone $roomAvailabilityRequest;
                $roomAvailabilityRequest->product->id = $roomAvailabilityExistent->componentGoldenId;
                $roomAvailabilityRequest->quantity = $roomAvailabilityExistent->stock;

                return $roomAvailabilityRequest;
            })($roomAvailabilityRequest, $roomAvailabilityExistent),
            $roomAvailabilityList,
        ];

        yield 'component-not-found-exception' => [
            $component,
            (function ($test) {
                $test->repository->findByComponentAndDateRange(Argument::any())->shouldNotBeCalled();
                $test->repository->save(Argument::cetera())->shouldNotBeCalled();
                $test->componentRepository->findOneByGoldenId(Argument::any())->willThrow(ComponentNotFoundException::class);
            }),
            (function ($roomAvailabilityRequest) {
                $roomAvailabilityRequest->product->id = '998877665';

                return $roomAvailabilityRequest;
            })(clone $roomAvailabilityRequest),
            null,
            ComponentNotFoundException::class,
        ];

        yield 'room-stock-type-not-valid-exception' => [
            (function ($component) {
                $component->roomStockType = null;

                return $component;
            })(clone $component),
            (function ($test, $roomAvailabilityList, $component) {
                $test->repository->findByComponentAndDateRange(Argument::any())->shouldNotBeCalled();
                $test->repository->save(Argument::cetera())->shouldNotBeCalled();
                $test->componentRepository->findOneByGoldenId(Argument::any())->willReturn($component);
            }),
            (function ($roomAvailabilityRequest) {
                $roomAvailabilityRequest->product->id = '998877665';

                return $roomAvailabilityRequest;
            })(clone $roomAvailabilityRequest),
            null,
            InvalidRoomStockTypeException::class,
        ];

        yield 'room-availability-create-request' => [
            $component,
            (function ($test, $roomAvailabilityList, $component, $roomAvailabilityRequest) {
                $diffDate = $roomAvailabilityRequest->dateTo->diff($roomAvailabilityRequest->dateFrom)->days + 1;
                $test->repository->save(Argument::cetera())->shouldBeCalledTimes($diffDate);
                $test->repository->findByComponentAndDateRange(Argument::cetera())->shouldBeCalled();
                $test->componentRepository->findOneByGoldenId(Argument::any())->willReturn($component);
            }),
            (function ($roomAvailabilityRequest) {
                $roomAvailabilityRequest->product->id = '999';
                $roomAvailabilityRequest->dateFrom = new \DateTime('tomorrow');
                $roomAvailabilityRequest->dateTo = new \DateTime('+ 3 days');

                return $roomAvailabilityRequest;
            })(clone $roomAvailabilityRequest),
        ];

        yield 'outdated-room-availability-exception' => [
            $component,
            (function ($test, $roomAvailabilityList, $component, $roomAvailabilityRequest, $logger) {
                $test->repository->findByComponentAndDateRange(Argument::cetera())->willReturn($roomAvailabilityList);
                $test->repository->save(Argument::cetera());
                $test->componentRepository->findOneByGoldenId(Argument::any())->willReturn($component);
                $logger->warning(
                    Argument::containingString(OutdatedRoomAvailabilityInformationException::class),
                    Argument::type('array'))
                    ->shouldBeCalled();
            }),
            (function ($roomAvailabilityRequest) {
                $roomAvailabilityRequest->product->id = '998877665';
                $roomAvailabilityRequest->updatedAt = new \DateTime('-2 month');

                return $roomAvailabilityRequest;
            })(clone $roomAvailabilityRequest),
            (function ($roomAvailabilityList) {
                foreach ($roomAvailabilityList as $roomAvailability) {
                    $roomAvailability->externalUpdatedAt->modify('-1 week');
                }

                return $roomAvailabilityList;
            })($roomAvailabilityList),
            null,
        ];
    }
}
