<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Constraint\RoomStockTypeConstraint;
use App\Contract\Request\Booking\BookingCreate\Experience as BookingCreateExperience;
use App\Contract\Request\Booking\BookingCreate\Guest;
use App\Contract\Request\Booking\BookingCreate\Room;
use App\Contract\Request\Booking\BookingCreate\RoomDate;
use App\Contract\Request\Booking\BookingCreateRequest;
use App\Contract\Request\Booking\BookingImport\BookingImportRequest;
use App\Contract\Request\Booking\BookingUpdateRequest;
use App\Entity\Booking;
use App\Entity\Box;
use App\Entity\BoxExperience;
use App\Entity\Component;
use App\Entity\Experience;
use App\Entity\Partner;
use App\Event\BookingStatusEvent;
use App\Exception\Booking\BadPriceException;
use App\Exception\Booking\BookingAlreadyInFinalStatusException;
use App\Exception\Booking\BookingHasExpiredException;
use App\Exception\Booking\CurrencyMismatchException;
use App\Exception\Booking\DateOutOfRangeException;
use App\Exception\Booking\DuplicatedDatesForSameRoomException;
use App\Exception\Booking\InvalidBoxBrandException;
use App\Exception\Booking\InvalidBoxCountryException;
use App\Exception\Booking\InvalidBoxCurrencyException;
use App\Exception\Booking\InvalidExperienceComponentListException;
use App\Exception\Booking\InvalidExtraNightException;
use App\Exception\Booking\MisconfiguredExperiencePriceException;
use App\Exception\Booking\NoIncludedRoomFoundException;
use App\Exception\Booking\RoomsDontHaveSameDurationException;
use App\Exception\Booking\UnallocatedDateException;
use App\Exception\Booking\UnavailableDateException;
use App\Exception\Http\ResourceConflictException;
use App\Exception\Repository\BookingNotFoundException;
use App\Helper\MoneyHelper;
use App\Manager\BookingManager;
use App\Repository\BookingRepository;
use App\Repository\BoxExperienceRepository;
use App\Repository\BoxRepository;
use App\Repository\ComponentRepository;
use App\Repository\ExperienceRepository;
use App\Repository\RoomAvailabilityRepository;
use App\Tests\ProphecyTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Money\Currency;
use Money\Money;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @coversDefaultClass \App\Manager\BookingManager
 * @group booking
 */
class BookingManagerTest extends ProphecyTestCase
{
    /**
     * @var EntityManagerInterface|ObjectProphecy
     */
    public $entityManager;

    /**
     * @var BookingRepository|ObjectProphecy
     */
    public $repository;

    /**
     * @var ExperienceRepository|ObjectProphecy
     */
    public $experienceRepository;

    /**
     * @var BoxExperienceRepository|ObjectProphecy
     */
    public $boxExperienceRepository;

    /**
     * @var ComponentRepository|ObjectProphecy
     */
    public $componentRepository;

    /**
     * @var MoneyHelper|ObjectProphecy
     */
    public $moneyHelper;

    /**
     * @var BoxRepository|ObjectProphecy
     */
    public $boxRepository;

    public BookingManager $bookingManager;

    /**
     * @var EventDispatcherInterface|ObjectProphecy
     */
    private $eventDispatcher;

    /**
     * @var RoomAvailabilityRepository | ObjectProphecy
     */
    private ObjectProphecy $roomAvailabilityRepository;

    public function setUp(): void
    {
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->repository = $this->prophesize(BookingRepository::class);
        $this->experienceRepository = $this->prophesize(ExperienceRepository::class);
        $this->boxExperienceRepository = $this->prophesize(BoxExperienceRepository::class);
        $this->componentRepository = $this->prophesize(ComponentRepository::class);
        $this->moneyHelper = $this->prophesize(MoneyHelper::class);
        $this->boxRepository = $this->prophesize(BoxRepository::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->roomAvailabilityRepository = $this->prophesize(RoomAvailabilityRepository::class);
        $this->bookingManager = new BookingManager(
            $this->entityManager->reveal(),
            $this->repository->reveal(),
            $this->experienceRepository->reveal(),
            $this->boxExperienceRepository->reveal(),
            $this->componentRepository->reveal(),
            $this->moneyHelper->reveal(),
            $this->boxRepository->reveal(),
            $this->eventDispatcher->reveal(),
            $this->roomAvailabilityRepository->reveal()
        );
    }

    /**
     * @covers ::update
     * @dataProvider dataForUpdate
     * @group update-booking
     */
    public function testUpdate(
        BookingUpdateRequest $bookingUpdateRequest,
        Booking $booking,
        ?string $exceptionClass,
        ?callable $asserts,
        ?callable $setUp
    ) {
        $this->repository->findOneByGoldenId($bookingUpdateRequest->bookingId)->willReturn($booking);
        if ($setUp) {
            $setUp($this);
        }

        if ($exceptionClass) {
            $this->expectException($exceptionClass);
        }

        $this->bookingManager->update($bookingUpdateRequest);
        if ($asserts) {
            $asserts($this, $booking);
        }
    }

    /**
     * @see testUpdate
     */
    public function dataForUpdate(): iterable
    {
        $bookingUpdateRequest = new BookingUpdateRequest();
        $bookingUpdateRequest->bookingId = '123123123';
        $bookingUpdateRequest->status = 'complete';
        $booking = new Booking();
        $booking->status = 'created';
        $booking->createdAt = new \DateTime('now');
        $booking->expiredAt = (clone $booking->createdAt)->add(new \DateInterval('PT15M'));

        yield 'happy path' => [
            $bookingUpdateRequest,
            $booking,
            null,
            function ($test, $booking) {
                $test->entityManager->persist($booking)->shouldHaveBeenCalled();
                $test->entityManager->flush()->shouldHaveBeenCalled();
            },
            function (BookingManagerTest $test) {
                $test->eventDispatcher
                    ->dispatch(Argument::type(BookingStatusEvent::class))
                    ->willReturn(Argument::type(BookingStatusEvent::class))
                ;
            },
        ];

        yield 'happy path updating voucher' => [
            (function ($bookingUpdateRequest) {
                $bookingUpdateRequest->voucher = '43214312';

                return $bookingUpdateRequest;
            })(clone $bookingUpdateRequest),
            clone $booking,
            null,
            function ($test, $booking) {
                $test->entityManager->persist($booking)->shouldHaveBeenCalled();
                $test->entityManager->flush()->shouldHaveBeenCalled();
                $test->eventDispatcher
                    ->dispatch(Argument::type(BookingStatusEvent::class))
                    ->willReturn(Argument::type(BookingStatusEvent::class))
                ;
                $test->assertEquals('43214312', $booking->voucher);
            },
            function (BookingManagerTest $test) {
                $test->eventDispatcher
                    ->dispatch(Argument::type(BookingStatusEvent::class))
                    ->willReturn(Argument::type(BookingStatusEvent::class))
                ;
            },
        ];

        yield 'booking in final status' => [
            $bookingUpdateRequest,
            (function ($booking) {
                $booking->status = 'cancelled';

                return $booking;
            })(clone $booking),
            BookingAlreadyInFinalStatusException::class,
            null,
            null,
        ];

        yield 'booking in final status receiving cancelled booking' => [
            (function ($bookingUpdateRequest) {
                $bookingUpdateRequest->status = 'cancelled';

                return $bookingUpdateRequest;
            })(clone $bookingUpdateRequest),
            (function ($booking) {
                $booking->status = 'cancelled';

                return $booking;
            })(clone $booking),
            null,
            function ($test, $booking) {
                $test->entityManager->persist($booking)->shouldNotHaveBeenCalled();
                $test->entityManager->flush()->shouldNotHaveBeenCalled();
                $test->eventDispatcher
                    ->dispatch(Argument::type(BookingStatusEvent::class))
                    ->willReturn(Argument::type(BookingStatusEvent::class))
                ;
                $test->assertEquals('cancelled', $booking->status);
            },
            function (BookingManagerTest $test) {
                $test->eventDispatcher
                    ->dispatch(Argument::type(BookingStatusEvent::class))
                    ->willReturn(Argument::type(BookingStatusEvent::class)
                    )->shouldNotHaveBeenCalled()
                ;
            },
        ];

        yield 'booking with date expired and no availability' => [
            (function ($bookingUpdateRequest) {
                $bookingUpdateRequest->status = 'complete';

                return $bookingUpdateRequest;
            })(clone $bookingUpdateRequest),
            (function ($booking) {
                $booking->status = 'created';
                $experience = $this->prophesize(Experience::class);
                $experience->goldenId = '59593';
                $booking->experience = $experience->reveal();
                $booking->experienceGoldenId = $experience->goldenId;
                $booking->expiredAt = clone $booking->createdAt;
                $booking->startDate = new \DateTime('2020-11-01');
                $booking->endDate = new \DateTime('2020-11-03');

                return $booking;
            })(clone $booking),
            BookingHasExpiredException::class,
            null,
            (function (BookingManagerTest $test) {
                $test->roomAvailabilityRepository->findBookingAvailabilityByExperienceAndDates(
                    Argument::type('string'),
                    Argument::type(\DateTimeInterface::class),
                    Argument::type(\DateTimeInterface::class)
                )->willReturn(
                    [
                        [
                            'experienceGoldenId' => '59593',
                            'componentGoldenId' => '213072',
                            'date' => '2020-11-02',
                            'realStock' => '1',
                            'usedStock' => '1',
                            'stock' => '2',
                        ],
                    ]
                );
            }),
        ];

        yield 'booking with date expired with availability' => [
            (function ($bookingUpdateRequest) {
                $bookingUpdateRequest->status = 'complete';

                return $bookingUpdateRequest;
            })(clone $bookingUpdateRequest),
            (function ($booking) {
                $booking->status = 'created';
                $experience = $this->prophesize(Experience::class);
                $experience->goldenId = '59593';
                $booking->experience = $experience->reveal();
                $booking->experienceGoldenId = $experience->goldenId;
                $booking->expiredAt = clone $booking->createdAt;
                $booking->startDate = new \DateTime('2020-11-01');
                $booking->endDate = new \DateTime('2020-11-03');

                return $booking;
            })(clone $booking),
            null,
            function ($test, $booking) {
                $test->entityManager->persist($booking)->shouldHaveBeenCalled();
                $test->entityManager->flush()->shouldHaveBeenCalled();
            },
            (function (BookingManagerTest $test) {
                $test->roomAvailabilityRepository->findBookingAvailabilityByExperienceAndDates(
                    Argument::type('string'),
                    Argument::type(\DateTimeInterface::class),
                    Argument::type(\DateTimeInterface::class)
                )->willReturn(
                    [
                        [
                            'experienceGoldenId' => '59593',
                            'componentGoldenId' => '213072',
                            'date' => '2020-11-01',
                            'realStock' => '1',
                            'usedStock' => '1',
                            'stock' => '2',
                        ],
                        [
                            'experienceGoldenId' => '59593',
                            'componentGoldenId' => '213072',
                            'date' => '2020-11-02',
                            'realStock' => '1',
                            'usedStock' => '1',
                            'stock' => '2',
                        ],
                        [
                            'experienceGoldenId' => '59593',
                            'componentGoldenId' => '213072',
                            'date' => '2020-11-03',
                            'realStock' => '2',
                            'usedStock' => '0',
                            'stock' => '2',
                        ],
                    ]
                );
                $test->eventDispatcher
                    ->dispatch(Argument::type(BookingStatusEvent::class))
                    ->willReturn(Argument::type(BookingStatusEvent::class))
                ;
            }),
        ];

        yield 'cancelling a completed booking with expired date' => [
            (static function ($bookingUpdateRequest) {
                $bookingUpdateRequest->status = 'cancelled';

                return $bookingUpdateRequest;
            })(clone $bookingUpdateRequest),
            (static function ($booking) {
                $booking->status = 'complete';
                $booking->expiredAt = clone $booking->createdAt;

                return $booking;
            })(clone $booking),
            null,
            function ($test, $booking) {
                $test->entityManager->persist($booking)->shouldHaveBeenCalled();
                $test->entityManager->flush()->shouldHaveBeenCalled();
                $test->eventDispatcher
                    ->dispatch(Argument::type(BookingStatusEvent::class))
                    ->willReturn(Argument::type(BookingStatusEvent::class))
                ;
                $test->assertEquals('cancelled', $booking->status);
            },
            function (BookingManagerTest $test) {
                $test->eventDispatcher
                    ->dispatch(Argument::type(BookingStatusEvent::class))
                    ->willReturn(Argument::type(BookingStatusEvent::class))
                ;
            },
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::create
     * @covers ::isUnavailableForBookings
     * @covers ::hasStopSaleOnRequestBookings
     * @group create
     *
     * @dataProvider dataForCreate
     */
    public function testCreate(
        BookingCreateRequest $bookingCreateRequest,
        ?int $duration,
        ?callable $setUp,
        ?string $exceptionClass,
        callable $asserts,
        array $extraParams = []
    ) {
        $partner = new Partner();
        $partner->goldenId = '5678';
        $partner->currency = $extraParams['partnerCurrency'] ?? 'EUR';

        $experience = new Experience();
        $experience->goldenId = $bookingCreateRequest->experience->id;
        $experience->partner = $partner;
        $experience->currency = $extraParams['currency'] ?? 'EUR';
        $experience->price = $extraParams['price'] ?? 500;
        $this->experienceRepository->findOneByGoldenId($bookingCreateRequest->experience->id)->willReturn($experience);

        $box = new Box();
        $box->goldenId = $bookingCreateRequest->box;
        $box->brand = $extraParams['boxBrand'] ?? 'SBX';
        $box->country = $extraParams['boxCountry'] ?? 'FR';
        $box->currency = $extraParams['boxCurrency'] ?? 'EUR';
        $this->boxRepository->findOneByGoldenId($bookingCreateRequest->box)->willReturn($box);

        $boxExperience = new BoxExperience();
        $this->boxExperienceRepository->findOneEnabledByBoxExperience($box, $experience)->willReturn($boxExperience);

        $component = new Component();
        $component->goldenId = 'component-id';
        $component->duration = $duration;
        $component->roomStockType = $extraParams['roomStockType'] ?? RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK;
        $this->componentRepository->findDefaultRoomByExperience($experience)->willReturn($component);

        $money = new Money($experience->price, new Currency($experience->currency));
        $this->moneyHelper->create($experience->price, $experience->currency)->willReturn($money);

        $bookingDates = [
            [
                'experienceGoldenId' => '59593',
                'componentGoldenId' => '213072',
                'date' => '2020-01-01',
                'realStock' => 7,
                'usedStock' => 2,
                'stock' => 9,
            ],
            [
                'experienceGoldenId' => '59593',
                'componentGoldenId' => '213072',
                'date' => '2020-01-02',
                'realStock' => 2,
                'usedStock' => 3,
                'stock' => 5,
            ],
            [
                'experienceGoldenId' => '59593',
                'componentGoldenId' => '213072',
                'date' => '2020-01-03',
                'realStock' => 5,
                'usedStock' => 0,
                'stock' => 5,
            ],
        ];

        $this->roomAvailabilityRepository->findBookingAvailabilityByExperienceAndDates(
            Argument::type('string'),
            Argument::type(\DateTimeInterface::class),
            Argument::type(\DateTimeInterface::class)
        )->willReturn($bookingDates);

        $bookingDates = [
            [
                'experienceGoldenId' => '59593',
                'componentGoldenId' => '213072',
                'date' => '2020-01-01',
                'realStock' => 7,
                'usedStock' => 2,
                'stock' => 9,
            ],
            [
                'experienceGoldenId' => '59593',
                'componentGoldenId' => '213072',
                'date' => '2020-01-02',
                'realStock' => 2,
                'usedStock' => 3,
                'stock' => 5,
            ],
            [
                'experienceGoldenId' => '59593',
                'componentGoldenId' => '213072',
                'date' => '2020-01-03',
                'realStock' => 5,
                'usedStock' => 0,
                'stock' => 5,
            ],
        ];

        $this->roomAvailabilityRepository->findBookingAvailabilityByExperienceAndDates(
            Argument::type('string'),
            Argument::type(\DateTimeInterface::class),
            Argument::type(\DateTimeInterface::class)
        )->willReturn($bookingDates);

        if ($setUp) {
            $setUp($this);
        }

        if ($exceptionClass) {
            $this->expectException($exceptionClass);
        }

        $booking = $this->bookingManager->create($bookingCreateRequest);

        $asserts($this, $booking);
    }

    /**
     * @see testCreate
     */
    public function dataForCreate(): iterable
    {
        $baseBookingCreateRequest = new BookingCreateRequest();
        $baseBookingCreateRequest->bookingId = 'SBXFRJBO200101123123';
        $baseBookingCreateRequest->box = '2406';
        $baseBookingCreateRequest->experience = new BookingCreateExperience();
        $baseBookingCreateRequest->experience->id = '3216334';
        $baseBookingCreateRequest->experience->components = [
            'Cup of tea',
            'Una noche muy buena',
        ];
        $baseBookingCreateRequest->currency = 'EUR';
        $baseBookingCreateRequest->voucher = '198257918';
        $baseBookingCreateRequest->startDate = new \DateTime('2020-01-01');
        $baseBookingCreateRequest->endDate = new \DateTime('2020-01-02');
        $baseBookingCreateRequest->customerComment = 'Clean sheets please';
        $baseBookingCreateRequest->guests = [new Guest()];
        $baseBookingCreateRequest->guests[0]->firstName = 'Hermano';
        $baseBookingCreateRequest->guests[0]->lastName = 'Guido';
        $baseBookingCreateRequest->guests[0]->email = 'maradona@worldcup.ar';
        $baseBookingCreateRequest->guests[0]->phone = '123 123 123';
        $baseBookingCreateRequest->guests[0]->isPrimary = true;
        $baseBookingCreateRequest->guests[0]->age = 30;
        $baseBookingCreateRequest->guests[0]->country = 'AR';

        yield 'happy days' => [
            (function ($bookingCreateRequest) {
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate];
                $bookingCreateRequest->rooms = [$room];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
                $test->eventDispatcher
                    ->dispatch(Argument::type(BookingStatusEvent::class))
                    ->willReturn(Argument::type(BookingStatusEvent::class))
                ;
                $test->roomAvailabilityRepository->findBookingAvailabilityByExperienceAndDates(
                    Argument::type('string'),
                    Argument::type(\DateTimeInterface::class),
                    Argument::type(\DateTimeInterface::class)
                )->shouldBeCalledOnce();
            },
            null,
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->flush()->shouldHaveBeenCalledOnce();
                $test->assertEquals(500, $booking->totalPrice);
                $test->assertCount(1, $booking->bookingDate);
                $test->assertCount(1, $booking->guest);
            },
        ];

        yield 'on-request happy days' => [
            (function ($bookingCreateRequest) {
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate];
                $bookingCreateRequest->rooms = [$room];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
                $test->eventDispatcher
                    ->dispatch(Argument::type(BookingStatusEvent::class))
                    ->willReturn(Argument::type(BookingStatusEvent::class))
                ;
                $test->roomAvailabilityRepository->findStopSaleOnRequestAvailabilityByExperienceAndDates(
                    Argument::type('string'),
                    Argument::type(\DateTimeInterface::class),
                    Argument::type(\DateTimeInterface::class)
                )->shouldBeCalledOnce();
                $test->roomAvailabilityRepository->findStopSaleOnRequestAvailabilityByExperienceAndDates(
                    Argument::type('string'),
                    Argument::type(\DateTimeInterface::class),
                    Argument::type(\DateTimeInterface::class)
                )->willReturn([]);
            },
            null,
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->flush()->shouldHaveBeenCalledOnce();
                $test->assertEquals(500, $booking->totalPrice);
                $test->assertCount(1, $booking->bookingDate);
                $test->assertCount(1, $booking->guest);
            },
            ['roomStockType' => RoomStockTypeConstraint::ROOM_STOCK_TYPE_ONREQUEST],
        ];

        yield 'on-request with stop sales day' => [
            (function ($bookingCreateRequest) {
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate];
                $bookingCreateRequest->rooms = [$room];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
                $test->eventDispatcher
                    ->dispatch(Argument::type(BookingStatusEvent::class))
                    ->willReturn(Argument::type(BookingStatusEvent::class))
                ;
                $test->roomAvailabilityRepository->findStopSaleOnRequestAvailabilityByExperienceAndDates(
                    Argument::type('string'),
                    Argument::type(\DateTimeInterface::class),
                    Argument::type(\DateTimeInterface::class)
                )->shouldBeCalledOnce();
                $test->roomAvailabilityRepository->findStopSaleOnRequestAvailabilityByExperienceAndDates(
                    Argument::type('string'),
                    Argument::type(\DateTimeInterface::class),
                    Argument::type(\DateTimeInterface::class)
                )->willReturn(
                    [
                    'experienceGoldenId' => '59593',
                    'componentGoldenId' => '213072',
                    'date' => '2020-01-01',
                    ]
                );
                $test->roomAvailabilityRepository->findBookingAvailabilityByExperienceAndDates()->shouldNotBeCalled();
            },
            UnavailableDateException::class,
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->flush()->shouldHaveBeenCalledOnce();
                $test->assertEquals(500, $booking->totalPrice);
                $test->assertCount(1, $booking->bookingDate);
                $test->assertCount(1, $booking->guest);
            },
            ['roomStockType' => RoomStockTypeConstraint::ROOM_STOCK_TYPE_ONREQUEST],
        ];

        yield 'on-request not so happy days' => [
            (function ($bookingCreateRequest) {
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate];
                $bookingCreateRequest->rooms = [$room];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
                $test->eventDispatcher
                    ->dispatch(Argument::type(BookingStatusEvent::class))
                    ->willReturn(Argument::type(BookingStatusEvent::class))
                ;
                $test->roomAvailabilityRepository->findStopSaleOnRequestAvailabilityByExperienceAndDates(
                    Argument::type('string'),
                    Argument::type(\DateTimeInterface::class),
                    Argument::type(\DateTimeInterface::class)
                )->willReturn(
                    [
                        [
                            'experienceGoldenId' => '59593',
                            'componentGoldenId' => '213072',
                            'date' => '2020-01-01',
                        ],
                        [
                            'experienceGoldenId' => '59593',
                            'componentGoldenId' => '213072',
                            'date' => '2020-01-02',
                        ],
                    ]
                );
            },
            UnavailableDateException::class,
            function ($test) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
            },
            ['roomStockType' => RoomStockTypeConstraint::ROOM_STOCK_TYPE_ONREQUEST],
        ];

        yield 'happy days, null duration (defaulting to one)' => [
            (function ($bookingCreateRequest) {
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate];
                $bookingCreateRequest->rooms = [$room];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            null,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
                $test->eventDispatcher
                    ->dispatch(Argument::type(BookingStatusEvent::class))
                    ->willReturn(Argument::type(BookingStatusEvent::class))
                ;
            },
            null,
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->flush()->shouldHaveBeenCalledOnce();
                $test->assertEquals(500, $booking->totalPrice);
                $test->assertCount(1, $booking->bookingDate);
                $test->assertCount(1, $booking->guest);
            },
        ];

        yield 'happy extra days' => [
            (function ($bookingCreateRequest) {
                $bookingCreateRequest->endDate = new \DateTime('2020-01-03');
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $roomDate2 = new RoomDate();
                $roomDate2->day = new \DateTime('2020-01-02');
                $roomDate2->price = 300;
                $roomDate2->extraNight = true;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate, $roomDate2];
                $bookingCreateRequest->rooms = [$room];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
                $test->eventDispatcher
                    ->dispatch(Argument::type(BookingStatusEvent::class))
                    ->willReturn(Argument::type(BookingStatusEvent::class))
                ;
                $test->eventDispatcher
                    ->dispatch(Argument::type(BookingStatusEvent::class))
                    ->shouldBeCalled()
                ;
            },
            null,
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->flush()->shouldHaveBeenCalledOnce();
                $test->assertEquals(800, $booking->totalPrice);
                $test->assertCount(2, $booking->bookingDate);
                $test->assertCount(1, $booking->guest);
            },
        ];

        yield 'happy extra rooms' => [
            (function ($bookingCreateRequest) {
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate];

                $roomDate2 = new RoomDate();
                $roomDate2->day = new \DateTime('2020-01-01');
                $roomDate2->price = 300;
                $roomDate2->extraNight = false;
                $room2 = new Room();
                $room2->extraRoom = true;
                $room2->dates = [$roomDate2];
                $bookingCreateRequest->rooms = [$room, $room2];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
                $test->eventDispatcher
                    ->dispatch(Argument::type(BookingStatusEvent::class))
                    ->willReturn(Argument::type(BookingStatusEvent::class))
                ;
                $test->eventDispatcher
                    ->dispatch(Argument::type(BookingStatusEvent::class))
                    ->shouldBeCalled()
                ;
            },
            null,
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->flush()->shouldHaveBeenCalledOnce();
                $test->assertEquals(800, $booking->totalPrice);
                $test->assertCount(2, $booking->bookingDate);
                $test->assertCount(1, $booking->guest);
            },
        ];

        yield 'happy extra days and extra rooms' => [
            (function ($bookingCreateRequest) {
                $bookingCreateRequest->endDate = new \DateTime('2020-01-03');
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $roomDate2 = new RoomDate();
                $roomDate2->day = new \DateTime('2020-01-02');
                $roomDate2->price = 300;
                $roomDate2->extraNight = true;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate, $roomDate2];

                $roomDate3 = new RoomDate();
                $roomDate3->day = new \DateTime('2020-01-01');
                $roomDate3->price = 300;
                $roomDate3->extraNight = false;
                $roomDate4 = new RoomDate();
                $roomDate4->day = new \DateTime('2020-01-02');
                $roomDate4->price = 300;
                $roomDate4->extraNight = true;
                $room2 = new Room();
                $room2->extraRoom = true;
                $room2->dates = [$roomDate3, $roomDate4];
                $bookingCreateRequest->rooms = [$room, $room2, $room2];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
                $test->eventDispatcher
                    ->dispatch(Argument::type(BookingStatusEvent::class))
                    ->willReturn(Argument::type(BookingStatusEvent::class))
                ;
                $test->eventDispatcher
                    ->dispatch(Argument::type(BookingStatusEvent::class))
                    ->shouldBeCalled()
                ;
            },
            null,
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->flush()->shouldHaveBeenCalledOnce();
                $test->assertEquals(2000, $booking->totalPrice);
                $test->assertCount(6, $booking->bookingDate);
                $test->assertCount(1, $booking->guest);
            },
        ];

        yield 'duplicated booking' => [
            (function ($bookingCreateRequest) {
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate];
                $bookingCreateRequest->rooms = [$room];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willReturn(new Booking());
            },
            ResourceConflictException::class,
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->flush()->shouldHaveBeenCalledOnce();
                $test->assertEquals(400, $booking->totalPrice);
                $test->assertCount(1, $booking->bookingDate);
                $test->assertCount(1, $booking->guest);
            },
        ];

        yield 'date not in range' => [
            (function ($bookingCreateRequest) {
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-03');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate];
                $bookingCreateRequest->rooms = [$room];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
            },
            DateOutOfRangeException::class,
            function ($test) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
            },
        ];

        yield 'duplicated dates for same room ' => [
            (function ($bookingCreateRequest) {
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $roomDate2 = new RoomDate();
                $roomDate2->day = new \DateTime('2020-01-01');
                $roomDate2->price = 0;
                $roomDate2->extraNight = false;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate, $roomDate2];

                $bookingCreateRequest->rooms = [$room];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
            },
            DuplicatedDatesForSameRoomException::class,
            function ($test) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
            },
        ];

        yield 'no included room ' => [
            (function ($bookingCreateRequest) {
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate];

                $bookingCreateRequest->rooms = [$room, $room];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
            },
            NoIncludedRoomFoundException::class,
            function ($test) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
            },
        ];

        yield 'dates not covering full room duration ' => [
            (function ($bookingCreateRequest) {
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate];

                $bookingCreateRequest->rooms = [$room];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            2,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
            },
            UnallocatedDateException::class,
            function ($test) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
            },
        ];

        yield 'unallocated date' => [
            (function ($bookingCreateRequest) {
                $bookingCreateRequest->endDate = new \DateTime('2020-01-03');
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate];

                $bookingCreateRequest->rooms = [$room];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            2,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
            },
            UnallocatedDateException::class,
            function ($test) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
            },
        ];

        yield 'rooms with different duration' => [
            (function ($bookingCreateRequest) {
                $bookingCreateRequest->endDate = new \DateTime('2020-01-03');
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $roomDate2 = new RoomDate();
                $roomDate2->day = new \DateTime('2020-01-02');
                $roomDate2->price = 0;
                $roomDate2->extraNight = false;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate, $roomDate2];

                $roomDate3 = new RoomDate();
                $roomDate3->day = new \DateTime('2020-01-02');
                $roomDate3->price = 3000;
                $roomDate3->extraNight = false;
                $room2 = new Room();
                $room2->extraRoom = true;
                $room2->dates = [$roomDate3];

                $bookingCreateRequest->rooms = [$room, $room2];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            2,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
            },
            RoomsDontHaveSameDurationException::class,
            function ($test) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
            },
        ];

        yield 'rooms with different duration 2' => [
            (function ($bookingCreateRequest) {
                $bookingCreateRequest->endDate = new \DateTime('2020-01-03');
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate];

                $roomDate2 = new RoomDate();
                $roomDate2->day = new \DateTime('2020-01-01');
                $roomDate2->price = 4000;
                $roomDate2->extraNight = false;
                $roomDate3 = new RoomDate();
                $roomDate3->day = new \DateTime('2020-01-02');
                $roomDate3->price = 3000;
                $roomDate3->extraNight = true;
                $room2 = new Room();
                $room2->extraRoom = true;
                $room2->dates = [$roomDate3, $roomDate2];

                $bookingCreateRequest->rooms = [$room, $room2];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            2,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
            },
            RoomsDontHaveSameDurationException::class,
            function ($test) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
            },
        ];

        yield 'extra night with price zero' => [
            (function ($bookingCreateRequest) {
                $bookingCreateRequest->endDate = new \DateTime('2020-01-03');
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $roomDate2 = new RoomDate();
                $roomDate2->day = new \DateTime('2020-01-02');
                $roomDate2->price = 0;
                $roomDate2->extraNight = true;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate, $roomDate2];

                $bookingCreateRequest->rooms = [$room];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::type('string'))->willThrow(new BookingNotFoundException());
            },
            BadPriceException::class,
            function ($test) {
                $test->entityManager->rollback()->shouldHaveBeenCalledTimes(1);
            },
        ];

        yield 'extra night with extra_night=false' => [
            (function ($bookingCreateRequest) {
                $bookingCreateRequest->endDate = new \DateTime('2020-01-03');
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $roomDate2 = new RoomDate();
                $roomDate2->day = new \DateTime('2020-01-02');
                $roomDate2->price = 1000;
                $roomDate2->extraNight = false;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate, $roomDate2];

                $bookingCreateRequest->rooms = [$room];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
            },
            InvalidExtraNightException::class,
            function ($test) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledTimes(1);
            },
        ];

        yield 'experience with price zero' => [
            (function ($bookingCreateRequest) {
                $bookingCreateRequest->endDate = new \DateTime('2020-01-03');
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $roomDate2 = new RoomDate();
                $roomDate2->day = new \DateTime('2020-01-02');
                $roomDate2->price = 0;
                $roomDate2->extraNight = true;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate, $roomDate2];

                $bookingCreateRequest->rooms = [$room];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::type('string'))->willThrow(new BookingNotFoundException());
            },
            MisconfiguredExperiencePriceException::class,
            function ($test) {
                $test->entityManager->rollback()->shouldHaveBeenCalledTimes(1);
            },
            ['price' => 0],
        ];

        yield 'experience with missing currency' => [
            (function ($bookingCreateRequest) {
                $bookingCreateRequest->endDate = new \DateTime('2020-01-03');
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $roomDate2 = new RoomDate();
                $roomDate2->day = new \DateTime('2020-01-02');
                $roomDate2->price = 0;
                $roomDate2->extraNight = true;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate, $roomDate2];

                $bookingCreateRequest->rooms = [$room];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::type('string'))->willThrow(new BookingNotFoundException());
            },
            MisconfiguredExperiencePriceException::class,
            function ($test) {
                $test->entityManager->rollback()->shouldHaveBeenCalledTimes(1);
            },
            ['currency' => '0'],
        ];

        yield 'box with missing currency' => [
            (function ($bookingCreateRequest) {
                $bookingCreateRequest->endDate = new \DateTime('2020-01-03');
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $roomDate2 = new RoomDate();
                $roomDate2->day = new \DateTime('2020-01-02');
                $roomDate2->price = 0;
                $roomDate2->extraNight = true;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate, $roomDate2];

                $bookingCreateRequest->rooms = [$room];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::type('string'))->willThrow(new BookingNotFoundException());
            },
            InvalidBoxCurrencyException::class,
            function ($test) {
                $test->entityManager->rollback()->shouldHaveBeenCalledTimes(1);
            },
            ['boxCurrency' => '0'],
        ];

        yield 'booking with upsell and different box and partner currency' => [
            (function ($bookingCreateRequest) {
                $bookingCreateRequest->endDate = new \DateTime('2020-01-03');
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $roomDate2 = new RoomDate();
                $roomDate2->day = new \DateTime('2020-01-02');
                $roomDate2->price = 300;
                $roomDate2->extraNight = true;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate, $roomDate2];

                $roomDate3 = new RoomDate();
                $roomDate3->day = new \DateTime('2020-01-01');
                $roomDate3->price = 300;
                $roomDate3->extraNight = false;
                $roomDate4 = new RoomDate();
                $roomDate4->day = new \DateTime('2020-01-02');
                $roomDate4->price = 300;
                $roomDate4->extraNight = true;
                $room2 = new Room();
                $room2->extraRoom = true;
                $room2->dates = [$roomDate3, $roomDate4];
                $bookingCreateRequest->rooms = [$room, $room2, $room2];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
            },
            CurrencyMismatchException::class,
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->flush()->shouldHaveBeenCalledOnce();
                $test->assertEquals(2000, $booking->totalPrice);
                $test->assertCount(6, $booking->bookingDate);
                $test->assertCount(1, $booking->guest);
            },
            [
                'partnerCurrency' => 'BRL',
            ],
        ];

        yield 'box with missing brand' => [
            (function ($bookingCreateRequest) {
                $bookingCreateRequest->endDate = new \DateTime('2020-01-03');
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $roomDate2 = new RoomDate();
                $roomDate2->day = new \DateTime('2020-01-02');
                $roomDate2->price = 0;
                $roomDate2->extraNight = true;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate, $roomDate2];

                $bookingCreateRequest->rooms = [$room];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::type('string'))->willThrow(new BookingNotFoundException());
            },
            InvalidBoxBrandException::class,
            function ($test) {
                $test->entityManager->rollback()->shouldHaveBeenCalledTimes(1);
            },
            ['boxBrand' => '0'],
        ];

        yield 'box with missing country' => [
            (function ($bookingCreateRequest) {
                $bookingCreateRequest->endDate = new \DateTime('2020-01-03');
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $roomDate2 = new RoomDate();
                $roomDate2->day = new \DateTime('2020-01-02');
                $roomDate2->price = 0;
                $roomDate2->extraNight = true;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate, $roomDate2];

                $bookingCreateRequest->rooms = [$room];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::type('string'))->willThrow(new BookingNotFoundException());
            },
            InvalidBoxCountryException::class,
            function ($test) {
                $test->entityManager->rollback()->shouldHaveBeenCalledTimes(1);
            },
            ['boxCountry' => '0'],
        ];

        yield 'booking without availability' => [
            (function ($bookingCreateRequest) {
                $bookingCreateRequest->startDate = new \DateTime('2020-11-01');
                $bookingCreateRequest->endDate = new \DateTime('2020-11-03');
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-11-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate];

                $bookingCreateRequest->rooms = [$room];

                return $bookingCreateRequest;
            })(clone $baseBookingCreateRequest),
            2,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
                $test->roomAvailabilityRepository->findBookingAvailabilityByExperienceAndDates(
                    Argument::type('string'),
                    Argument::type(\DateTimeInterface::class),
                    Argument::type(\DateTimeInterface::class)
                )->willReturn(
                    [
                        [
                            'experienceGoldenId' => '59593',
                            'componentGoldenId' => '213072',
                            'date' => new \DateTime('2020-11-02'),
                            'realStock' => '1',
                            'usedStock' => '1',
                            'stock' => '2',
                        ],
                    ]
                );
            },
            UnavailableDateException::class,
            function ($test) {
                $test->roomAvailabilityRepository->findBookingAvailabilityByExperienceAndDates(
                    Argument::type('string'),
                    Argument::type(\DateTimeInterface::class),
                    Argument::type(\DateTimeInterface::class)
                )->shouldBeCalled();
            },
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::create
     *
     * @group create
     */
    public function testCreateWithoutComponent()
    {
        $bookingCreateRequest = new BookingCreateRequest();
        $bookingCreateRequest->experience = new \App\Contract\Request\Booking\BookingCreate\Experience();

        $this->expectException(InvalidExperienceComponentListException::class);

        $this->bookingManager->create($bookingCreateRequest);
    }

    /**
     * @covers ::__construct
     * @covers ::import
     * @dataProvider dataForImport
     */
    public function testImport(
        BookingImportRequest $bookingCreateRequest,
        ?int $duration,
        ?callable $setUp,
        ?string $exceptionClass,
        callable $asserts,
        array $extraParams = []
    ) {
        $partner = new Partner();
        $partner->goldenId = '5678';
        $partner->currency = $extraParams['partnerCurrency'] ?? 'EUR';

        $experience = new Experience();
        $experience->goldenId = $bookingCreateRequest->experience->id;
        $experience->partner = $partner;
        $experience->price = $extraParams['price'] ?? 500;
        $this->experienceRepository->findOneByGoldenId($bookingCreateRequest->experience->id)->willReturn($experience);

        $box = new Box();
        $box->goldenId = $bookingCreateRequest->box;
        $box->brand = $extraParams['boxBrand'] ?? 'SBX';
        $box->country = $extraParams['boxCountry'] ?? 'FR';
        $box->currency = 'EUR';
        $this->boxRepository->findOneByGoldenId($bookingCreateRequest->box)->willReturn($box);

        $boxExperience = new BoxExperience();
        $this->boxExperienceRepository->findOneEnabledByBoxExperience($box, $experience)->willReturn($boxExperience);

        $component = new Component();
        $component->goldenId = 'component-id';
        $component->duration = $duration;
        $this->componentRepository->findAnyRoomByExperience($experience)->willReturn($component);

        $money = new Money($experience->price, new Currency($bookingCreateRequest->currency));
        $this->moneyHelper->create($experience->price, $bookingCreateRequest->currency)->willReturn($money);

        if ($setUp) {
            $setUp($this);
        }

        if ($exceptionClass) {
            $this->expectException($exceptionClass);
        }

        $booking = $this->bookingManager->import($bookingCreateRequest);

        $asserts($this, $booking);
    }

    /**
     * @see testImport
     */
    public function dataForImport()
    {
        $bookingImportRequest = new BookingImportRequest();
        $bookingImportRequest->bookingId = 'SBXFRJBO200101123123';
        $bookingImportRequest->box = '2406';
        $bookingImportRequest->experience = new BookingCreateExperience();
        $bookingImportRequest->experience->id = '3216334';
        $bookingImportRequest->experience->components = [
            'Cup of tea',
            'Una noche muy buena',
        ];
        $bookingImportRequest->currency = 'EUR';
        $bookingImportRequest->voucher = '198257918';
        $bookingImportRequest->startDate = new \DateTime('2020-01-01');
        $bookingImportRequest->endDate = new \DateTime('2020-01-02');
        $bookingImportRequest->customerComment = 'Clean sheets please';
        $bookingImportRequest->guests = [new \App\Contract\Request\Booking\BookingImport\Guest()];
        $bookingImportRequest->guests[0]->firstName = 'Hermano';
        $bookingImportRequest->guests[0]->lastName = 'Guido';
        $bookingImportRequest->guests[0]->email = 'maradona@worldcup.ar';
        $bookingImportRequest->guests[0]->phone = '123 123 123';
        $bookingImportRequest->guests[0]->isPrimary = true;
        $bookingImportRequest->guests[0]->age = 30;
        $bookingImportRequest->guests[0]->country = 'AR';

        yield 'happy days' => [
            (function ($bookingImportRequest) {
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 10;
                $roomDate->extraNight = false;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate];
                $bookingImportRequest->rooms = [$room];

                return $bookingImportRequest;
            })(clone $bookingImportRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
            },
            null,
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->flush()->shouldHaveBeenCalledOnce();
                $test->assertEquals(10, $booking->totalPrice);
                $test->assertCount(1, $booking->bookingDate);
                $test->assertCount(1, $booking->guest);
            },
        ];

        yield 'happy days, null duration (defaulting to one)' => [
            (function ($bookingImportRequest) {
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate];
                $bookingImportRequest->rooms = [$room];

                return $bookingImportRequest;
            })(clone $bookingImportRequest),
            null,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
            },
            null,
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->flush()->shouldHaveBeenCalledOnce();
                $test->assertEquals(0, $booking->totalPrice);
                $test->assertCount(1, $booking->bookingDate);
                $test->assertCount(1, $booking->guest);
            },
        ];

        yield 'happy extra days' => [
            (function ($bookingImportRequest) {
                $bookingImportRequest->endDate = new \DateTime('2020-01-03');
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $roomDate2 = new RoomDate();
                $roomDate2->day = new \DateTime('2020-01-02');
                $roomDate2->price = 300;
                $roomDate2->extraNight = true;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate, $roomDate2];
                $bookingImportRequest->rooms = [$room];

                return $bookingImportRequest;
            })(clone $bookingImportRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
            },
            null,
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->flush()->shouldHaveBeenCalledOnce();
                $test->assertEquals(300, $booking->totalPrice);
                $test->assertCount(2, $booking->bookingDate);
                $test->assertCount(1, $booking->guest);
            },
        ];

        yield 'happy extra rooms' => [
            (function ($bookingImportRequest) {
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate];

                $roomDate2 = new RoomDate();
                $roomDate2->day = new \DateTime('2020-01-01');
                $roomDate2->price = 300;
                $roomDate2->extraNight = false;
                $room2 = new Room();
                $room2->extraRoom = true;
                $room2->dates = [$roomDate2];
                $bookingImportRequest->rooms = [$room, $room2];

                return $bookingImportRequest;
            })(clone $bookingImportRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
            },
            null,
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->flush()->shouldHaveBeenCalledOnce();
                $test->assertEquals(300, $booking->totalPrice);
                $test->assertCount(2, $booking->bookingDate);
                $test->assertCount(1, $booking->guest);
            },
        ];

        yield 'happy extra days and extra rooms' => [
            (function ($bookingImportRequest) {
                $bookingImportRequest->endDate = new \DateTime('2020-01-03');
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $roomDate2 = new RoomDate();
                $roomDate2->day = new \DateTime('2020-01-02');
                $roomDate2->price = 300;
                $roomDate2->extraNight = true;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate, $roomDate2];

                $roomDate3 = new RoomDate();
                $roomDate3->day = new \DateTime('2020-01-01');
                $roomDate3->price = 300;
                $roomDate3->extraNight = false;
                $roomDate4 = new RoomDate();
                $roomDate4->day = new \DateTime('2020-01-02');
                $roomDate4->price = 300;
                $roomDate4->extraNight = true;
                $room2 = new Room();
                $room2->extraRoom = true;
                $room2->dates = [$roomDate3, $roomDate4];
                $bookingImportRequest->rooms = [$room, $room2, $room2];

                return $bookingImportRequest;
            })(clone $bookingImportRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
            },
            null,
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->flush()->shouldHaveBeenCalledOnce();
                $test->assertEquals(1500, $booking->totalPrice);
                $test->assertCount(6, $booking->bookingDate);
                $test->assertCount(1, $booking->guest);
            },
        ];

        yield 'duplicated booking' => [
            (function ($bookingImportRequest) {
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate];
                $bookingImportRequest->rooms = [$room];

                return $bookingImportRequest;
            })(clone $bookingImportRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::any())->willReturn(new Booking());
            },
            ResourceConflictException::class,
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->flush()->shouldHaveBeenCalledOnce();
                $test->assertEquals(400, $booking->totalPrice);
                $test->assertCount(1, $booking->bookingDate);
                $test->assertCount(1, $booking->guest);
            },
        ];

        yield 'box with missing brand' => [
            (function ($bookingImportRequest) {
                $bookingImportRequest->endDate = new \DateTime('2020-01-03');
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $roomDate2 = new RoomDate();
                $roomDate2->day = new \DateTime('2020-01-02');
                $roomDate2->price = 0;
                $roomDate2->extraNight = true;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate, $roomDate2];

                $bookingImportRequest->rooms = [$room];

                return $bookingImportRequest;
            })(clone $bookingImportRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::type('string'))->willThrow(new BookingNotFoundException());
            },
            InvalidBoxBrandException::class,
            function ($test) {
                $test->entityManager->rollback()->shouldHaveBeenCalledTimes(1);
            },
            ['boxBrand' => '0'],
        ];

        yield 'box with missing country' => [
            (function ($bookingImportRequest) {
                $bookingImportRequest->endDate = new \DateTime('2020-01-03');
                $roomDate = new RoomDate();
                $roomDate->day = new \DateTime('2020-01-01');
                $roomDate->price = 0;
                $roomDate->extraNight = false;
                $roomDate2 = new RoomDate();
                $roomDate2->day = new \DateTime('2020-01-02');
                $roomDate2->price = 0;
                $roomDate2->extraNight = true;
                $room = new Room();
                $room->extraRoom = false;
                $room->dates = [$roomDate, $roomDate2];

                $bookingImportRequest->rooms = [$room];

                return $bookingImportRequest;
            })(clone $bookingImportRequest),
            1,
            function (BookingManagerTest $test) {
                $test->repository->findOneByGoldenId(Argument::type('string'))->willThrow(new BookingNotFoundException());
            },
            InvalidBoxCountryException::class,
            function ($test) {
                $test->entityManager->rollback()->shouldHaveBeenCalledTimes(1);
            },
            ['boxCountry' => '0'],
        ];
    }
}
