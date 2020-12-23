<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Constants\DateTimeConstants;
use App\Constraint\RoomStockTypeConstraint;
use App\Contract\Request\BroadcastListener\Product\Product;
use App\Contract\Request\BroadcastListener\RoomAvailabilityRequest;
use App\Contract\Request\BroadcastListener\RoomAvailabilityRequestList;
use App\Entity\Booking;
use App\Entity\BookingDate;
use App\Entity\Box;
use App\Entity\BoxExperience;
use App\Entity\Component;
use App\Entity\Experience;
use App\Entity\ExperienceComponent;
use App\Entity\Guest;
use App\Entity\Partner;
use App\Entity\RoomAvailability;
use App\Exception\Manager\RoomAvailability\OutdatedRoomAvailabilityInformationException;
use App\Exception\Repository\ComponentNotFoundException;
use App\Manager\RoomAvailabilityManager;
use App\Repository\ComponentRepository;
use App\Repository\RoomAvailabilityRepository;
use App\Tests\ProphecyTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \App\Manager\RoomAvailabilityManager
 * @group room-availability-manager
 */
class RoomAvailabilityManagerTest extends ProphecyTestCase
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
     * @var EntityManagerInterface|ObjectProphecy
     */
    protected $em;

    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    /**
     * @var MessageBusInterface|ObjectProphecy
     */
    private $messageBus;

    /**
     * @var EventDispatcherInterface|ObjectProphecy
     */
    private $eventDispatcher;

    private RoomAvailabilityManager $manager;

    public function setUp(): void
    {
        $this->repository = $this->prophesize(RoomAvailabilityRepository::class);
        $this->componentRepository = $this->prophesize(ComponentRepository::class);
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->manager = new RoomAvailabilityManager(
            $this->repository->reveal(),
            $this->componentRepository->reveal(),
            $this->em->reveal(),
            $this->messageBus->reveal(),
            $this->eventDispatcher->reveal(),
            $this->logger->reveal()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAndPriceAvailabilitiesByExperienceIdAndDates
     */
    public function testGetRoomandPriceAvailabilitiesByExperienceId()
    {
        $roomAvais = [
            '1234', '4321', '1111',
        ];
        $this->repository->findAvailableRoomsAndPricesByExperienceIdAndDates(
            '1234',
            new \DateTime('2020-06-20'),
            new \DateTime('2020-06-30')
            )->willReturn($roomAvais)
            ->shouldBeCalled()
        ;
        $this->manager->getRoomAndPriceAvailabilitiesByExperienceIdAndDates(
            '1234',
            new \DateTime('2020-06-20'),
            new \DateTime('2020-06-30')
        );
    }

    /**
     * @covers ::dispatchRoomAvailabilitiesRequest
     */
    public function testDispatchRoomAvailabilitiesRequest(): void
    {
        $product = new Product();
        $product->id = '299994';
        $roomAvailabilityRequestList = new RoomAvailabilityRequestList();
        $roomAvailabilityRequest = new RoomAvailabilityRequest();

        $roomAvailabilityRequest->product = $product;
        $roomAvailabilityRequest->quantity = 2;
        $roomAvailabilityRequest->dateFrom = new \DateTime('+5 days');
        $roomAvailabilityRequest->dateTo = new \DateTime('+8 days');
        $roomAvailabilityRequest->updatedAt = new \DateTime('now');

        $roomAvailabilityRequest2 = (clone $roomAvailabilityRequest);
        $roomAvailabilityRequest2->product = clone $product;
        $roomAvailabilityRequest2->product->id = '218439';
        $roomAvailabilityRequest2->quantity = 5;

        $roomAvailabilityRequest3 = (clone $roomAvailabilityRequest);
        $roomAvailabilityRequest3->product = clone $product;
        $roomAvailabilityRequest3->product->id = '315172';
        $roomAvailabilityRequest3->quantity = 1;

        $roomAvailabilityRequestList->items = [
            $roomAvailabilityRequest,
            $roomAvailabilityRequest2,
            $roomAvailabilityRequest3,
        ];

        $this->componentRepository->filterManageableComponetsByComponentId(['299994', '218439', '315172'])->willReturn(['299994' => [], '218439' => []]);

        $this
            ->messageBus
            ->dispatch(Argument::is($roomAvailabilityRequest))->willReturn(new Envelope(new \stdClass()))
            ->shouldBeCalled();
        $this
            ->messageBus
            ->dispatch(Argument::is($roomAvailabilityRequest2))->willReturn(new Envelope(new \stdClass()))
            ->shouldBeCalled();
        $this
            ->messageBus
            ->dispatch(Argument::is($roomAvailabilityRequest3))->willReturn(new Envelope(new \stdClass()))
            ->shouldNotBeCalled();

        $this
            ->logger
            ->warning('Received room availability for unknown component', $roomAvailabilityRequest3->getContext())
            ->shouldBeCalled();

        $this->manager->dispatchRoomAvailabilitiesRequest($roomAvailabilityRequestList);
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAvailabilitiesByMultipleExperienceGoldenIds
     */
    public function testGetRoomAvailabilitiesByMultipleExperienceGoldenIds()
    {
        $experienceGoldenIds = ['1234', '5678'];
        $startDate = new \DateTime('2020-10-01');

        $result = [
            '1234' => [
                'experience_golden_id' => '1234',
                'partner_golden_id' => '147852',
                'duration' => '1',
                'is_sellable' => '1',
            ],
            '5678' => [
                'experience_golden_id' => '5678',
                'partner_golden_id' => '147852',
                'duration' => '1',
                'is_sellable' => '1',
            ],
        ];

        $this
            ->repository
            ->findAvailableRoomsByMultipleExperienceIds($experienceGoldenIds, $startDate)
            ->shouldBeCalled()
            ->willReturn($result);

        $this->assertEquals(
            $result,
            $this->manager->getRoomAvailabilitiesByMultipleExperienceGoldenIds($experienceGoldenIds, $startDate)
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAvailabilitiesByBoxId
     */
    public function testGetRoomAvailabilitiesByBoxId()
    {
        $boxId = '1234';
        $this->repository->findAvailableRoomsByBoxId($boxId, Argument::type(\DateTime::class))
            ->willReturn(
                [
                    [
                        'roomAvailabilities' => 'stock,stock,stock',
                        'experienceGoldenId' => '1234',
                    ],
                    [
                        'roomAvailabilities' => 'on_request,on_request,on_request',
                        'experienceGoldenId' => '1235',
                    ],
                    [
                        'roomAvailabilities' => 'stock,stock,on_request',
                        'experienceGoldenId' => '1236',
                    ],
                ]
            );
        $this->manager->getRoomAvailabilitiesByBoxId($boxId, new \DateTime('2020-06-20'));

        $this->repository->findAvailableRoomsByBoxId($boxId, new \DateTime('2020-06-20'))
            ->shouldBeCalledOnce();
    }

    /**
     * @dataProvider roomAvailabilityRequestProvider
     * @covers ::replace
     * @covers ::hasAvailabilityChangedForBoxCache
     * @covers ::canRoomAvailabilityBeDeleted
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

        $this->assertNull($this->manager->replace($roomAvailabilityRequest));
    }

    public function roomAvailabilityRequestProvider()
    {
        $component = new Component();
        $component->goldenId = '238506';
        $component->roomStockType = 'stock';

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
        $roomAvailabilityExistent->date = $roomAvailabilityRequest->dateFrom;
        $roomAvailabilityExistent->stock = 2;
        $roomAvailabilityExistent->externalUpdatedAt = new \DateTime('-2 days');

        $date = $roomAvailabilityRequest->dateFrom;

        $date2 = (clone $date)->modify('+1 day');
        $roomAvailabilityExistent2 = clone $roomAvailabilityExistent;
        $roomAvailabilityExistent2->date = $date2;
        $roomAvailabilityExistent2->externalUpdatedAt = clone $roomAvailabilityExistent->externalUpdatedAt;

        $date3 = (clone $date)->modify('+2 days');
        $roomAvailabilityExistent3 = clone $roomAvailabilityExistent;
        $roomAvailabilityExistent3->date = $date3;
        $roomAvailabilityExistent3->externalUpdatedAt = clone $roomAvailabilityExistent->externalUpdatedAt;

        $date4 = (clone $date)->modify('+3 days');
        $roomAvailabilityExistent4 = clone $roomAvailabilityExistent;
        $roomAvailabilityExistent4->date = $date4;
        $roomAvailabilityExistent4->externalUpdatedAt = clone $roomAvailabilityExistent->externalUpdatedAt;

        $roomAvailabilityList = [
            $date->format(DateTimeConstants::DEFAULT_DATE_FORMAT) => $roomAvailabilityExistent,
            $date2->format(DateTimeConstants::DEFAULT_DATE_FORMAT) => $roomAvailabilityExistent2,
            $date3->format(DateTimeConstants::DEFAULT_DATE_FORMAT) => $roomAvailabilityExistent3,
            $date4->format(DateTimeConstants::DEFAULT_DATE_FORMAT) => $roomAvailabilityExistent4,
        ];

        yield 'room-availability-update-with-no-meaningful-change' => [
            $component,
            (function ($test, $roomAvailabilityList, $component) {
                $test->repository->deleteByComponentIdAndDateRange(
                    Argument::type(Component::class),
                    Argument::type(\DateTime::class),
                    Argument::type(\DateTime::class)
                )->shouldBeCalledOnce();
                $test->repository->findByComponentAndDateRange(
                    Argument::type(Component::class),
                    Argument::type(\DateTime::class),
                    Argument::type(\DateTime::class)
                )->shouldNotBeCalled();
                $test->em->flush()->shouldBeCalledTimes(0);
                $test->em->persist(Argument::type(RoomAvailability::class))->shouldBeCalledTimes(0);
                $test->componentRepository->findOneByGoldenId(Argument::type('string'))->willReturn($component);
            }),
            (function ($roomAvailabilityRequest) {
                $roomAvailabilityRequest->quantity = 0;
                $roomAvailabilityRequest->updatedAt->modify('now');
                $roomAvailabilityRequest->isStopSale = false;

                return $roomAvailabilityRequest;
            })(clone $roomAvailabilityRequest),
            (function ($roomAvailabilityList) {
                foreach ($roomAvailabilityList as $roomAvailability) {
                    $roomAvailability->externalUpdatedAt->modify('-1 week');
                }

                return $roomAvailabilityList;
            })($roomAvailabilityList),
        ];

        yield 'room-availability-update-request-with-meaningful-changes' => [
            $component,
            (function ($test, $roomAvailabilityList, $component) {
                $test->repository->findByComponentAndDateRange(
                    Argument::type(Component::class),
                    Argument::type(\DateTime::class),
                    Argument::type(\DateTime::class)
                )->willReturn($roomAvailabilityList);
                $test->em->flush()->shouldBeCalledTimes(1);
                $test->em->persist(Argument::type(RoomAvailability::class))->shouldBeCalledTimes(3);
                $test->componentRepository->findOneByGoldenId(Argument::type('string'))->willReturn($component);
            }),
            (function ($roomAvailabilityRequest) {
                //zeroing the stock, so we may need to recalculate some stuff
                $roomAvailabilityRequest->quantity = 0;
                $roomAvailabilityRequest->updatedAt->modify('now');
                $roomAvailabilityRequest->isStopSale = true;

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
                $test->repository->findByComponentAndDateRange(
                    Argument::type(Component::class),
                    Argument::type(\DateTime::class),
                    Argument::type(\DateTime::class)
                )->willReturn($roomAvailabilityList);
                $test->em->flush()->shouldBeCalledTimes(1);
                $test->em->persist(Argument::type(RoomAvailability::class))->shouldBeCalledTimes(3);
                $test->componentRepository->findOneByGoldenId(Argument::type('string'))->willReturn($component);
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
                $test->repository->findByComponentAndDateRange(
                    Argument::type(Component::class),
                    Argument::type(\DateTime::class),
                    Argument::type(\DateTime::class)
                )->shouldNotBeCalled();
                $test->em->flush()->shouldNotBeCalled();
                $test->em->persist()->shouldNotBeCalled();
                $test->componentRepository->findOneByGoldenId(Argument::type('string'))
                    ->willThrow(ComponentNotFoundException::class);
            }),
            (function ($roomAvailabilityRequest) {
                $roomAvailabilityRequest->product->id = '998877665';

                return $roomAvailabilityRequest;
            })(clone $roomAvailabilityRequest),
            null,
            ComponentNotFoundException::class,
        ];

        yield 'room-availability-create-request' => [
            $component,
            (function ($test, $roomAvailabilityList, $component, $roomAvailabilityRequest) {
                $diffDate = $roomAvailabilityRequest->dateTo->diff($roomAvailabilityRequest->dateFrom)->days + 1;
                $test->em->flush()->shouldBeCalledTimes(1);
                $test->em->persist(Argument::type(RoomAvailability::class))->shouldBeCalledTimes($diffDate);
                $test->repository->findByComponentAndDateRange(
                    Argument::type(Component::class),
                    Argument::type(\DateTime::class),
                    Argument::type(\DateTime::class)
                )->shouldBeCalled();
                $test->componentRepository->findOneByGoldenId(Argument::type('string'))->willReturn($component);
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
                $test->repository->findByComponentAndDateRange(
                    Argument::type(Component::class),
                    Argument::type(\DateTime::class),
                    Argument::type(\DateTime::class)
                )->willReturn($roomAvailabilityList);
                $test->em->flush()->shouldBeCalledTimes(1);
                $test->em->persist(Argument::type(RoomAvailability::class))->shouldNotBeCalled();
                $test->componentRepository->findOneByGoldenId(Argument::type('string'))->willReturn($component);
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
                    $roomAvailability->externalUpdatedAt = (clone $roomAvailability->externalUpdatedAt)->modify('-1 week');
                }

                return $roomAvailabilityList;
            })($roomAvailabilityList),
            null,
        ];

        yield 'room-availability-create-request-with-on-request-component' => [
            (function (Component $component) {
                $component->goldenId = '218439';
                $component->roomStockType = 'on_request';

                return $component;
            })(clone $component),
            (function ($test, $roomAvailabilityList, $component, $roomAvailabilityRequest) {
                $diffDate = $roomAvailabilityRequest->dateTo->diff($roomAvailabilityRequest->dateFrom)->days + 1;
                $test->em->flush()->shouldBeCalledTimes(1);
                $test->em->persist(Argument::type(RoomAvailability::class))->shouldBeCalledTimes($diffDate);
                $test->repository->findByComponentAndDateRange(
                    Argument::type(Component::class),
                    Argument::type(\DateTime::class),
                    Argument::type(\DateTime::class)
                )->shouldBeCalled();
                $test->componentRepository->findOneByGoldenId(Argument::type('string'))->willReturn($component);
            }),
            (function ($roomAvailabilityRequest) {
                $roomAvailabilityRequest->product->id = '999';
                $roomAvailabilityRequest->dateFrom = new \DateTime('tomorrow');
                $roomAvailabilityRequest->dateTo = new \DateTime('+ 3 days');

                return $roomAvailabilityRequest;
            })(clone $roomAvailabilityRequest),
        ];

        yield 'room-availability-update-request-with-on-request-component' => [
            (function (Component $component) {
                $component->goldenId = '218439';
                $component->roomStockType = 'on_request';

                return $component;
            })(clone $component),
            (function ($test, $roomAvailabilityList, $component) {
                $test->repository->findByComponentAndDateRange(
                    Argument::type(Component::class),
                    Argument::type(\DateTime::class),
                    Argument::type(\DateTime::class)
                )->shouldBeCalledOnce()->willReturn($roomAvailabilityList);
                $test->em->flush()->shouldBeCalledOnce();
                $test->em->persist(Argument::type(RoomAvailability::class))->shouldBeCalledTimes(3);
                $test->componentRepository->findOneByGoldenId(Argument::type('string'))
                    ->shouldBeCalledOnce()
                    ->willReturn($component);
            }),
            (function ($roomAvailabilityRequest) {
                $roomAvailabilityRequest->quantity = 1;
                $roomAvailabilityRequest->updatedAt->modify('now');
                $roomAvailabilityRequest->isStopSale = false;

                return $roomAvailabilityRequest;
            })(clone $roomAvailabilityRequest),
            $roomAvailabilityList,
        ];
    }

    /**
     * @dataProvider confirmationBookingProvider
     */
    public function testUpdateStockBookingConfirmation(
        Booking $booking,
        RoomAvailability $availability,
        callable $prophecies,
        ?int $numberOfRooms = 1
    ) {
        $prophecies($this, $availability, $numberOfRooms);

        $response = $this->manager->updateStockBookingConfirmation($booking);
        $this->assertNull($response);
    }

    public function confirmationBookingProvider()
    {
        $populateBookingDates = function (Booking $booking, int $rooms = 1) {
            $period = new \DatePeriod($booking->startDate, new \DateInterval('P1D'), $booking->endDate);
            $booking->bookingDate = new ArrayCollection();
            for ($i = 0; $i < $rooms; ++$i) {
                foreach ($period as $date) {
                    $bookingDate = new BookingDate();
                    $bookingDate->componentGoldenId = '5464';
                    $bookingDate->date = $date;
                    $bookingDate->price = 1212;
                    $booking->bookingDate->add($bookingDate);
                }
            }
        };

        $booking = new Booking();
        $booking->voucher = '198257918';
        $booking->goldenId = '12345';
        $dateTime = new \DateTime('2020-10-01');
        $booking->startDate = new \DateTime('2020-10-01');
        $booking->createdAt = $dateTime;
        $booking->updatedAt = $dateTime;
        $booking->expiredAt = (new $dateTime())->modify('+15 minutes');
        $booking->voucher = '1234154';
        $booking->partnerGoldenId = '1234154';
        $booking->experienceGoldenId = '1234154';
        $booking->components = [
            'name' => 'name',
        ];

        $experienceComponent = $this->prophesize(ExperienceComponent::class);
        $component = new Component();
        $component->goldenId = '5464';
        $component->name = 'component name';
        $component->roomStockType = RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK;
        $experienceComponent->component = $component;

        $boxExperience = $this->prophesize(BoxExperience::class);
        $box = $this->prophesize(Box::class);
        $box->country = 'FR';
        $boxExperience->box = $box->reveal();

        $experience = $this->prophesize(Experience::class);
        $experience->price = 125;
        $experience->experienceComponent = new ArrayCollection([$experienceComponent->reveal()]);
        $experience->boxExperience = new ArrayCollection([$boxExperience->reveal()]);
        $booking->experience = $experience->reveal();

        $partner = $this->prophesize(Partner::class);
        $partner->currency = 'EUR';
        $booking->partner = $partner->reveal();

        $guest = $this->prophesize(Guest::class);
        $guest->firstName = 'First Name';
        $guest->lastName = 'Last Name';
        $guest->phone = '089 585 5555';
        $guest->email = 'teste@teste.com';
        $booking->guest = new ArrayCollection([$guest->reveal()]);

        $availability = new RoomAvailability();
        $availability->componentGoldenId = '5464';
        $availability->date = $dateTime;
        $availability->stock = 10;
        $availability->component = $component;

        yield 'update-booking-with-success' => [
            (function ($booking) use ($populateBookingDates) {
                $booking->startDate = new \DateTime('2020-10-01');
                $booking->endDate = (clone $booking->startDate)->modify('+1 day');
                $populateBookingDates($booking);

                return $booking;
            })(clone $booking),
            $availability,
            (function ($test, $availability, $increment) {
                $availability->stock = 10;
                $component = $availability->component;
                $component->roomStockType = RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK;
                $test->componentRepository
                    ->findOneByGoldenId(
                        Argument::type('string')
                    )->willReturn($component);
                $test->repository
                    ->findAllByComponentGoldenIdAndDates(
                        Argument::type('string'),
                        Argument::type('array')
                    )->willReturn([
                        '2020-10-01' => $availability,
                    ]);
                $test->repository
                    ->updateStocksForAvailability(
                        Argument::type('string'),
                        Argument::type('array'),
                        $increment
                    )->shouldBeCalledTimes(1);
            }),
        ];

        yield 'update-booking-with-on-request-booking' => [
            (function ($booking) use ($populateBookingDates) {
                $booking->startDate = new \DateTime('2020-10-01');
                $booking->endDate = (clone $booking->startDate)->modify('+1 day');
                $populateBookingDates($booking);

                return $booking;
            })(clone $booking),
            $availability,
            (function ($test, $availability, $increment) {
                $availability->stock = 10;
                $component = $availability->component;
                $component->roomStockType = RoomStockTypeConstraint::ROOM_STOCK_TYPE_ONREQUEST;
                $test->componentRepository
                    ->findOneByGoldenId(
                        Argument::type('string')
                    )->willReturn($component);
                $test->repository
                    ->findAllByComponentGoldenIdAndDates(
                        Argument::type('string'),
                        Argument::type('array')
                    )->shouldNotBeCalled();
                $test->repository
                    ->updateStocksForAvailability(
                        Argument::type('string'),
                        Argument::type('array'),
                        $increment
                    )->shouldNotBeCalled();
            }),
        ];

        yield 'update-bookings-big-range' => [
            (function ($booking) use ($populateBookingDates) {
                $booking->endDate = (clone $booking->startDate)->modify('+10 day');
                $populateBookingDates($booking);

                return $booking;
            })(clone $booking),
            $availability,
            (function ($test, $availability, $increment) {
                $availability->stock = 10;
                $component = $availability->component;
                $component->roomStockType = RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK;
                $test->componentRepository
                    ->findOneByGoldenId(
                        Argument::type('string')
                    )->willReturn($component);

                $test->repository
                    ->findAllByComponentGoldenIdAndDates(
                        Argument::type('string'),
                        Argument::type('array')
                    )->willReturn([
                        '2020-10-01' => $availability,
                        '2020-10-02' => $availability,
                        '2020-10-03' => $availability,
                        '2020-10-04' => $availability,
                        '2020-10-05' => $availability,
                        '2020-10-06' => $availability,
                        '2020-10-07' => $availability,
                        '2020-10-08' => $availability,
                        '2020-10-09' => $availability,
                        '2020-10-10' => $availability,
                    ]);
                $test->repository
                    ->updateStocksForAvailability(
                        Argument::type('string'),
                        Argument::type('array'),
                        $increment
                    )->shouldBeCalledTimes(1);
            }),
        ];

        yield 'update-bookings-with-extra-room' => [
            (function ($booking, $dateTime) use ($populateBookingDates) {
                $booking->endDate = (clone $booking->startDate)->modify('+1 day');
                $populateBookingDates($booking, 2);

                return $booking;
            })(clone $booking, $dateTime),
            $availability,
            (function ($test, $availability, $increment) {
                $availability->stock = 10;
                $component = $availability->component;
                $component->roomStockType = RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK;
                $test->componentRepository
                    ->findOneByGoldenId(
                        Argument::type('string')
                    )->willReturn($component);
                $test->repository
                    ->findAllByComponentGoldenIdAndDates(
                        Argument::type('string'),
                        Argument::type('array')
                    )->willReturn([
                        '2020-10-01' => $availability,
                    ]);
                $test->repository
                    ->updateStocksForAvailability(
                        Argument::type('string'),
                        Argument::type('array'),
                        $increment
                    )->shouldBeCalledTimes(1);
            }),
            2,
        ];

        yield 'update-booking-with-no-room-availability' => [
            (function ($booking) use ($populateBookingDates) {
                $booking->startDate = new \DateTime('2020-10-01');
                $booking->endDate = (clone $booking->startDate)->modify('+1 day');
                $populateBookingDates($booking);

                return $booking;
            })(clone $booking),
            $availability,
            (function ($test, $availability, $increment) {
                $component = $availability->component;
                $component->roomStockType = RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK;
                $test->componentRepository
                    ->findOneByGoldenId(
                        Argument::type('string')
                    )->willReturn($component);
                $test->repository
                    ->findAllByComponentGoldenIdAndDates(
                        Argument::type('string'),
                        Argument::type('array')
                    )->willReturn([]);
                $test->repository
                    ->updateStocksForAvailability(
                        Argument::type('string'),
                        Argument::type('array'),
                        $increment
                    )->shouldNotBeCalled();
                $test->em->remove($availability)->shouldNotBeCalled();
                $test->em->flush()->shouldNotBeCalled();
            }),
        ];

        yield 'update-booking-with-no-exist-date-room-availability' => [
            (function ($booking) use ($populateBookingDates) {
                $booking->startDate = new \DateTime('2020-10-01');
                $booking->endDate = (clone $booking->startDate)->modify('+1 day');
                $populateBookingDates($booking);

                return $booking;
            })(clone $booking),
            $availability,
            (function ($test, $availability, $increment) {
                $component = $availability->component;
                $component->roomStockType = RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK;
                $test->componentRepository
                    ->findOneByGoldenId(
                        Argument::type('string')
                    )->willReturn($component);
                $test->repository
                    ->findAllByComponentGoldenIdAndDates(
                        Argument::type('string'),
                        Argument::type('array')
                    )->willReturn([
                        '11-11-2099' => $availability,
                    ]);
                $test->repository
                    ->updateStocksForAvailability(
                        Argument::type('string'),
                        Argument::type('array'),
                        $increment
                    )->shouldNotBeCalled();
                $test->em->remove($availability)->shouldNotBeCalled();
                $test->em->persist(Argument::type(RoomAvailability::class))->shouldNotBeCalled();
                $test->em->persist(Argument::type(Booking::class))->shouldNotBeCalled();
                $test->em->flush()->shouldBeCalledTimes(1);
            }),
        ];

        yield 'update-booking-with-stock-1' => [
            (function ($booking) use ($populateBookingDates) {
                $booking->startDate = new \DateTime('2020-10-01');
                $booking->endDate = (clone $booking->startDate)->modify('+1 day');
                $populateBookingDates($booking);

                return $booking;
            })(clone $booking),
            $availability,
            (function ($test, $availability, $increment) {
                $availability->stock = 1;
                $component = $availability->component;
                $component->roomStockType = RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK;
                $test->componentRepository
                    ->findOneByGoldenId(
                        Argument::type('string')
                    )->willReturn($component);
                $test->repository
                    ->findAllByComponentGoldenIdAndDates(
                        Argument::type('string'),
                        Argument::type('array')
                    )->willReturn([
                        '2020-10-01' => $availability,
                    ]);
                $test->repository
                    ->updateStocksForAvailability(
                        Argument::type('string'),
                        Argument::type('array'),
                        $increment
                    )->shouldNotBeCalled();
                $test->em->remove($availability)->shouldBeCalledTimes(1);
                $test->em->flush()->shouldBeCalledTimes(1);
            }),
        ];

        yield 'update-booking-with-stock-0' => [
            (function ($booking) use ($populateBookingDates) {
                $booking->startDate = new \DateTime('2020-10-01');
                $booking->endDate = (clone $booking->startDate)->modify('+1 day');
                $populateBookingDates($booking);

                return $booking;
            })(clone $booking),
            $availability,
            (function ($test, $availability, $increment) {
                $availability->stock = 0;
                $component = $availability->component;
                $component->roomStockType = RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK;
                $test->componentRepository
                    ->findOneByGoldenId(
                        Argument::type('string')
                    )->willReturn($component);
                $test->repository
                    ->findAllByComponentGoldenIdAndDates(
                        Argument::type('string'),
                        Argument::type('array')
                    )->willReturn([
                        '2020-10-01' => $availability,
                    ]);
                $test->repository
                    ->updateStocksForAvailability(
                        Argument::type('string'),
                        Argument::type('array'),
                        $increment
                    )->shouldNotBeCalled();
                $test->em->remove($availability)->shouldBeCalledTimes(1);
                $test->em->flush()->shouldBeCalledTimes(1);
            }),
        ];
    }

    /**
     * @dataProvider cancellationBookingProvider
     */
    public function testUpdateStockBookingCancellation(
        Booking $booking,
        RoomAvailability $availability,
        callable $prophecies,
        ?int $numberOfRooms = 1
    ) {
        $prophecies($this, $availability, -$numberOfRooms);

        $response = $this->manager->updateStockBookingCancellation($booking);
        $this->assertNull($response);
    }

    public function cancellationBookingProvider()
    {
        $populateBookingDates = function (Booking $booking, int $rooms = 1) {
            $period = new \DatePeriod($booking->startDate, new \DateInterval('P1D'), $booking->endDate);
            $booking->bookingDate = new ArrayCollection();
            for ($i = 0; $i < $rooms; ++$i) {
                foreach ($period as $date) {
                    $bookingDate = new BookingDate();
                    $bookingDate->componentGoldenId = '5464';
                    $bookingDate->date = $date;
                    $bookingDate->price = 1212;
                    $booking->bookingDate->add($bookingDate);
                }
            }
        };

        $booking = new Booking();
        $booking->voucher = '198257918';
        $booking->goldenId = '12345';
        $dateTime = new \DateTime('2020-10-01');
        $booking->startDate = new \DateTime('2020-10-01');
        $booking->createdAt = $dateTime;
        $booking->updatedAt = $dateTime;
        $booking->expiredAt = (new $dateTime())->modify('+15 minutes');
        $booking->voucher = '1234154';
        $booking->partnerGoldenId = '1234154';
        $booking->experienceGoldenId = '1234154';
        $booking->components = [
            'name' => 'name',
        ];

        $experienceComponent = $this->prophesize(ExperienceComponent::class);
        $component = new Component();
        $component->goldenId = '5464';
        $component->name = 'component name';
        $component->roomStockType = RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK;
        $experienceComponent->component = $component;

        $boxExperience = $this->prophesize(BoxExperience::class);
        $box = $this->prophesize(Box::class);
        $box->country = 'FR';
        $boxExperience->box = $box->reveal();

        $experience = $this->prophesize(Experience::class);
        $experience->price = 125;
        $experience->experienceComponent = new ArrayCollection([$experienceComponent->reveal()]);
        $experience->boxExperience = new ArrayCollection([$boxExperience->reveal()]);
        $booking->experience = $experience->reveal();

        $partner = $this->prophesize(Partner::class);
        $partner->currency = 'EUR';
        $booking->partner = $partner->reveal();

        $guest = $this->prophesize(Guest::class);
        $guest->firstName = 'First Name';
        $guest->lastName = 'Last Name';
        $guest->phone = '089 585 5555';
        $guest->email = 'teste@teste.com';
        $booking->guest = new ArrayCollection([$guest->reveal()]);

        $availability = new RoomAvailability();
        $availability->componentGoldenId = '5464';
        $availability->date = $dateTime;
        $availability->stock = 10;
        $availability->component = $component;

        yield 'update-booking-with-success' => [
            (function ($booking) use ($populateBookingDates) {
                $booking->startDate = new \DateTime('2020-10-01');
                $booking->endDate = (clone $booking->startDate)->modify('+1 day');
                $populateBookingDates($booking);

                return $booking;
            })(clone $booking),
            $availability,
            (function ($test, $availability, $increment) {
                $availability->stock = 10;
                $component = $availability->component;
                $component->roomStockType = RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK;
                $test->componentRepository
                    ->findOneByGoldenId(
                        Argument::type('string')
                    )->willReturn($component);
                $test->repository
                    ->findAllByComponentGoldenIdAndDates(
                        Argument::type('string'),
                        Argument::type('array')
                    )->willReturn([
                        '2020-10-01' => $availability,
                    ]);
                $test->repository
                    ->updateStocksForAvailability(
                        Argument::type('string'),
                        Argument::type('array'),
                        $increment
                    )->shouldBeCalledTimes(1);
            }),
        ];

        yield 'update-booking-with-on-request-booking' => [
            (function ($booking) use ($populateBookingDates) {
                $booking->startDate = new \DateTime('2020-10-01');
                $booking->endDate = (clone $booking->startDate)->modify('+1 day');
                $populateBookingDates($booking);

                return $booking;
            })(clone $booking),
            $availability,
            (function ($test, $availability, $increment) {
                $availability->stock = 10;
                $component = $availability->component;
                $component->roomStockType = RoomStockTypeConstraint::ROOM_STOCK_TYPE_ONREQUEST;
                $test->componentRepository
                    ->findOneByGoldenId(
                        Argument::type('string')
                    )->willReturn($component);
                $test->repository
                    ->findAllByComponentGoldenIdAndDates(
                        Argument::type('string'),
                        Argument::type('array')
                    )->shouldNotBeCalled();
                $test->repository
                    ->updateStocksForAvailability(
                        Argument::type('string'),
                        Argument::type('array'),
                        $increment
                    )->shouldNotBeCalled();
            }),
        ];

        yield 'update-bookings-big-range' => [
            (function ($booking) use ($populateBookingDates) {
                $booking->endDate = (clone $booking->startDate)->modify('+10 day');
                $populateBookingDates($booking);

                return $booking;
            })(clone $booking),
            $availability,
            (function ($test, $availability, $increment) {
                $availability->stock = 10;
                $component = $availability->component;
                $component->roomStockType = RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK;
                $test->componentRepository
                    ->findOneByGoldenId(
                        Argument::type('string')
                    )->willReturn($component);
                $test->repository
                    ->findAllByComponentGoldenIdAndDates(
                        Argument::type('string'),
                        Argument::type('array')
                    )->willReturn([
                        '2020-10-01' => $availability,
                    ]);
                $test->repository
                    ->updateStocksForAvailability(
                        Argument::type('string'),
                        Argument::type('array'),
                        $increment
                    )->shouldBeCalledTimes(1);
            }),
        ];

        yield 'update-bookings-with-extra-room' => [
            (function ($booking, $dateTime) use ($populateBookingDates) {
                $booking->endDate = (clone $booking->startDate)->modify('+1 day');
                $populateBookingDates($booking, 2);

                return $booking;
            })(clone $booking, $dateTime),
            $availability,
            (function ($test, $availability, $increment) {
                $availability->stock = 10;
                $component = $availability->component;
                $component->roomStockType = RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK;
                $test->componentRepository
                    ->findOneByGoldenId(
                        Argument::type('string')
                    )->willReturn($component);
                $test->repository
                    ->findAllByComponentGoldenIdAndDates(
                        Argument::type('string'),
                        Argument::type('array')
                    )->willReturn([
                        '2020-10-01' => $availability,
                    ]);
                $test->repository
                    ->updateStocksForAvailability(
                        Argument::type('string'),
                        Argument::type('array'),
                        $increment
                    )->shouldBeCalledTimes(1);
            }),
            2,
        ];

        yield 'update-booking-with-no-room-availability' => [
            (function ($booking) use ($populateBookingDates) {
                $booking->startDate = new \DateTime('2020-10-01');
                $booking->endDate = (clone $booking->startDate)->modify('+1 day');
                $populateBookingDates($booking);

                return $booking;
            })(clone $booking),
            $availability,
            (function ($test, $availability, $increment) {
                $availability->stock = 1;
                $component = $availability->component;
                $component->roomStockType = RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK;
                $test->componentRepository
                    ->findOneByGoldenId(
                        Argument::type('string')
                    )->willReturn($component);
                $test->repository
                    ->findAllByComponentGoldenIdAndDates(
                        Argument::type('string'),
                        Argument::type('array')
                    )->willReturn([]);
                $test->repository
                    ->updateStocksForAvailability(
                        Argument::type('string'),
                        Argument::type('array'),
                        $increment
                    )->shouldNotBeCalled();
                $test->em->persist(Argument::any())->shouldBeCalledTimes(1);
                $test->em->flush()->shouldBeCalledTimes(1);
            }),
        ];

        yield 'update-booking-with-stock-1' => [
            (function ($booking) use ($populateBookingDates) {
                $booking->startDate = new \DateTime('2020-10-01');
                $booking->endDate = (clone $booking->startDate)->modify('+1 day');
                $populateBookingDates($booking);

                return $booking;
            })(clone $booking),
            $availability,
            (function ($test, $availability, $increment) {
                $availability->stock = 1;
                $component = $availability->component;
                $component->roomStockType = RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK;
                $test->componentRepository
                    ->findOneByGoldenId(
                        Argument::type('string')
                    )->willReturn($component);
                $test->repository
                    ->findAllByComponentGoldenIdAndDates(
                        Argument::type('string'),
                        Argument::type('array')
                    )->willReturn([
                        '2020-10-01' => $availability,
                    ]);
                $test->repository
                    ->updateStocksForAvailability(
                        Argument::type('string'),
                        Argument::type('array'),
                        $increment
                    )->shouldBeCalledTimes(1);
                $test->em->persist($availability)
                    ->shouldNotBeCalled();
                $test->em->flush()->shouldBeCalledTimes(1);
            }),
        ];

        yield 'update-booking-with-stock-0' => [
            (function ($booking) use ($populateBookingDates) {
                $booking->startDate = new \DateTime('2020-10-01');
                $booking->endDate = (clone $booking->startDate)->modify('+1 day');
                $populateBookingDates($booking);

                return $booking;
            })(clone $booking),
            $availability,
            (function ($test, $availability, $increment) {
                $availability->stock = 1;
                $component = $availability->component;
                $component->roomStockType = RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK;
                $test->componentRepository
                    ->findOneByGoldenId(
                        Argument::type('string')
                    )->willReturn($component);
                $test->repository
                    ->findAllByComponentGoldenIdAndDates(
                        Argument::type('string'),
                        Argument::type('array')
                    )->willReturn([
                        '2020-10-01' => $availability,
                    ]);
                $test->repository
                    ->updateStocksForAvailability(
                        Argument::type('string'),
                        Argument::type('array'),
                        $increment
                    )->shouldBeCalledTimes(1);
                $test->em->persist($availability)
                    ->shouldNotBeCalled();
                $test->em->flush()->shouldBeCalledTimes(1);
            }),
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAndPriceAvailabilitiesByExperienceIdAndDates
     */
    public function testGetRoomAndPriceAvailabilitiesByExperienceIdAndDates(): void
    {
        $roomAndPriceAvailabilities = [
            0 => [
                'Date' => '2020-10-01T00:00:00.000000',
                'AvailabilityValue' => '1',
                'type' => 'stock',
                'isStopSale' => '0',
                'duration' => '1',
                'SellingPrice' => '86.45',
                'BuyingPrice' => '86.45',
                'lastBookableDate' => null,
            ],
        ];
        $this->repository->findAvailableRoomsAndPricesByExperienceIdAndDates(
            '1234',
            new \DateTime('2020-10-01'),
            new \DateTime('2020-10-03')
        )
            ->shouldBeCalledOnce()
            ->willReturn($roomAndPriceAvailabilities)
        ;
        $this->assertCount(
            1,
            $this->manager->getRoomAndPriceAvailabilitiesByExperienceIdAndDates(
                '1234',
                new \DateTime('2020-10-01'),
                new \DateTime('2020-10-03')
            ));
    }
}
