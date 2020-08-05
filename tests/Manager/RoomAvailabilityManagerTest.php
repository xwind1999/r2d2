<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\BroadcastListener\Product\Product;
use App\Contract\Request\BroadcastListener\RoomAvailabilityRequest;
use App\Contract\Request\Internal\RoomAvailability\RoomAvailabilityCreateRequest;
use App\Contract\Request\Internal\RoomAvailability\RoomAvailabilityUpdateRequest;
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
use Ramsey\Uuid\UuidInterface;

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
     * @covers ::get
     * @covers ::update
     */
    public function testUpdate()
    {
        $manager = new RoomAvailabilityManager($this->repository->reveal(), $this->componentRepository->reveal(), $this->logger->reveal());
        $component = new Component();
        $component->goldenId = '1234';
        $this->componentRepository->findOneByGoldenId(Argument::any())->willReturn($component);
        $roomAvailabilityUpdateRequest = new RoomAvailabilityUpdateRequest();
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $roomAvailabilityUpdateRequest->componentGoldenId = '1234';
        $roomAvailabilityUpdateRequest->stock = 2;
        $roomAvailabilityUpdateRequest->date = new \DateTime('2020-01-01');
        $roomAvailabilityUpdateRequest->type = 'instant';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);

        $roomAvailability = new RoomAvailability();
        $roomAvailability->uuid = $uuidInterface->reveal();
        $roomAvailability->componentGoldenId = '4321';
        $roomAvailability->stock = 3;
        $roomAvailability->date = new \DateTime('2020-01-02');
        $roomAvailability->type = 'instant';
        $this->repository->findOne($uuid)->willReturn($roomAvailability);

        $this->repository->save(Argument::type(RoomAvailability::class))->shouldBeCalled();

        $updatedRoomAvailability = $manager->update($uuid, $roomAvailabilityUpdateRequest);

        $this->assertSame($roomAvailability, $updatedRoomAvailability);
        $this->assertEquals('1234', $roomAvailability->componentGoldenId);
        $this->assertEquals('2', $roomAvailability->stock);
        $this->assertEquals('2020-01-01', $roomAvailability->date->format('Y-m-d'));
        $this->assertEquals('instant', $roomAvailability->type);
    }

    /**
     * @covers ::__construct
     * @covers ::delete
     */
    public function testDelete()
    {
        $manager = new RoomAvailabilityManager($this->repository->reveal(), $this->componentRepository->reveal(), $this->logger->reveal());
        $uuid = '12345678';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $roomAvailability = new RoomAvailability();
        $roomAvailability->uuid = $uuidInterface->reveal();
        $this->repository->findOne($uuid)->willReturn($roomAvailability);

        $this->repository->delete(Argument::type(RoomAvailability::class))->shouldBeCalled();

        $manager->delete($uuid);
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {
        $manager = new RoomAvailabilityManager($this->repository->reveal(), $this->componentRepository->reveal(), $this->logger->reveal());
        $uuid = '12345678';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $roomAvailability = new RoomAvailability();
        $roomAvailability->uuid = $uuidInterface->reveal();
        $this->repository->findOne($uuid)->willReturn($roomAvailability);

        $actual = $manager->get($uuid);

        $this->assertSame($roomAvailability, $actual);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreate()
    {
        $manager = new RoomAvailabilityManager($this->repository->reveal(), $this->componentRepository->reveal(), $this->logger->reveal());
        $component = new Component();
        $component->goldenId = '1234';
        $this->componentRepository->findOneByGoldenId(Argument::any())->willReturn($component);
        $roomAvailabilityCreateRequest = new RoomAvailabilityCreateRequest();
        $roomAvailabilityCreateRequest->componentGoldenId = '1234';
        $roomAvailabilityCreateRequest->stock = 2;
        $roomAvailabilityCreateRequest->date = new \DateTime('2020-01-01');
        $roomAvailabilityCreateRequest->type = 'instant';

        $this->repository->save(Argument::type(RoomAvailability::class))->shouldBeCalled();

        $roomAvailability = $manager->create($roomAvailabilityCreateRequest);
        $this->assertEquals($roomAvailabilityCreateRequest->componentGoldenId, $roomAvailability->componentGoldenId);
        $this->assertEquals($roomAvailabilityCreateRequest->stock, $roomAvailability->stock);
        $this->assertEquals($roomAvailabilityCreateRequest->date, $roomAvailability->date);
        $this->assertEquals($roomAvailabilityCreateRequest->type, $roomAvailability->type);
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAvailabilitiesByComponentGoldenIds
     */
    public function testGetRoomAvailabilitiesByComponentGoldenIds()
    {
        $compIds = [
            '1234', '4321', '1111',
        ];
        $this->repository->findRoomAvailabilitiesByComponentGoldenIds(Argument::any(), Argument::any(), Argument::any(), Argument::any())->willReturn($compIds);
        $manager = new RoomAvailabilityManager($this->repository->reveal(), $this->componentRepository->reveal(), $this->logger->reveal());
        $manager->getRoomAvailabilitiesByComponentGoldenIds($compIds, 'instant', new \DateTime('2020-06-20'), new \DateTime('2020-06-30'));

        $this->repository->findRoomAvailabilitiesByComponentGoldenIds($compIds, 'instant', new \DateTime('2020-06-20'), new \DateTime('2020-06-30'))->shouldBeCalledOnce();
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAvailabilitiesListByComponentGoldenId
     */
    public function testGetRoomAvailabilitiesListByComponentGoldenId()
    {
        $this->repository->findAllByComponentGoldenId(Argument::any(), Argument::any(), Argument::any(), Argument::any())
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
        $manager->getRoomAvailabilitiesListByComponentGoldenId('1234', 'instant', new \DateTime('2020-06-20'), new \DateTime('2020-06-30'));

        $this->repository->findAllByComponentGoldenId('1234', 'instant', new \DateTime('2020-06-20'), new \DateTime('2020-06-30'))
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
        $roomAvailabilityRequest->dateFrom = new \DateTime('+10 days');
        $roomAvailabilityRequest->dateTo = (clone $roomAvailabilityRequest->dateFrom)->modify('+3 days');
        $roomAvailabilityRequest->dateTimeUpdated = new \DateTime('now');

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
                $roomAvailabilityRequest->dateTimeUpdated->modify('now');

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
                $roomAvailabilityRequest->dateTimeUpdated = new \DateTime('-2 month');

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
