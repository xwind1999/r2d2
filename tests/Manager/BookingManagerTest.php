<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\Booking\BookingCreate\Room;
use App\Contract\Request\Booking\BookingCreate\RoomDate;
use App\Contract\Request\Booking\BookingCreateRequest;
use App\Entity\Booking;
use App\Entity\BookingDate;
use App\Entity\Box;
use App\Entity\BoxExperience;
use App\Entity\Component;
use App\Entity\Experience;
use App\Entity\Guest;
use App\Entity\Partner;
use App\Exception\Booking\BadPriceException;
use App\Exception\Booking\DateOutOfRangeException;
use App\Exception\Booking\DuplicatedDatesForSameRoomException;
use App\Exception\Booking\InvalidExtraNightException;
use App\Exception\Booking\NoIncludedRoomFoundException;
use App\Exception\Booking\RoomsDontHaveSameDurationException;
use App\Exception\Booking\UnallocatedDateException;
use App\Exception\Http\ResourceConflictException;
use App\Exception\Repository\BookingNotFoundException;
use App\Helper\MoneyHelper;
use App\Manager\BookingManager;
use App\Repository\BookingRepository;
use App\Repository\BoxExperienceRepository;
use App\Repository\BoxRepository;
use App\Repository\ComponentRepository;
use App\Repository\ExperienceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \App\Manager\BookingManager
 */
class BookingManagerTest extends TestCase
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

    public function setUp(): void
    {
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->repository = $this->prophesize(BookingRepository::class);
        $this->experienceRepository = $this->prophesize(ExperienceRepository::class);
        $this->boxExperienceRepository = $this->prophesize(BoxExperienceRepository::class);
        $this->componentRepository = $this->prophesize(ComponentRepository::class);
        $this->moneyHelper = $this->prophesize(MoneyHelper::class);
        $this->boxRepository = $this->prophesize(BoxRepository::class);
        $this->bookingManager = new BookingManager(
            $this->entityManager->reveal(),
            $this->repository->reveal(),
            $this->experienceRepository->reveal(),
            $this->boxExperienceRepository->reveal(),
            $this->componentRepository->reveal(),
            $this->moneyHelper->reveal(),
            $this->boxRepository->reveal()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::create
     *
     * @dataProvider dataForCreate
     */
    public function testCreate(BookingCreateRequest $bookingCreateRequest, ?int $duration, ?callable $setUp, ?string $exceptionClass, callable $asserts)
    {
        $partner = new Partner();
        $partner->goldenId = '5678';

        $experience = new Experience();
        $experience->goldenId = $bookingCreateRequest->experience->id;
        $experience->partner = $partner;
        $this->experienceRepository->findOneByGoldenId($bookingCreateRequest->experience->id)->willReturn($experience);

        $box = new Box();
        $box->goldenId = $bookingCreateRequest->box;
        $box->brand = 'SBX';
        $box->country = 'FR';
        $this->boxRepository->findOneByGoldenId($bookingCreateRequest->box)->willReturn($box);

        $boxExperience = new BoxExperience();
        $this->boxExperienceRepository->findOneEnabledByBoxExperience($box, $experience)->willReturn($boxExperience);

        $component = new Component();
        $component->goldenId = 'component-id';
        $component->duration = $duration;
        $this->componentRepository->findDefaultRoomByExperience($experience)->willReturn($component);

        // TODO: replace with experience price after R2D2-209
        $money = new Money(400, new Currency($bookingCreateRequest->currency));
        $this->moneyHelper->create(400, $bookingCreateRequest->currency)->willReturn($money);

        if ($setUp) {
            $setUp($this);
        }

        if ($exceptionClass) {
            $this->expectException($exceptionClass);
        }

        $booking = $this->bookingManager->create($bookingCreateRequest);

        $asserts($this, $booking);
    }

    public function dataForCreate(): iterable
    {
        $baseBookingCreateRequest = new BookingCreateRequest();
        $baseBookingCreateRequest->bookingId = 'SBXFRJBO200101123123';
        $baseBookingCreateRequest->box = '2406';
        $baseBookingCreateRequest->experience = new \App\Contract\Request\Booking\BookingCreate\Experience();
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
        $baseBookingCreateRequest->guests = [new \App\Contract\Request\Booking\BookingCreate\Guest()];
        $baseBookingCreateRequest->guests[0]->firstName = 'Hermano';
        $baseBookingCreateRequest->guests[0]->lastName = 'Guido';
        $baseBookingCreateRequest->guests[0]->email = 'maradona@worldcup.ar';
        $baseBookingCreateRequest->guests[0]->phone = '123 123 123';

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
            },
            null,
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(BookingDate::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->persist(Argument::type(Guest::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->flush()->shouldHaveBeenCalledOnce();
                $test->assertEquals(400, $booking->totalPrice);
                $test->assertCount(1, $booking->dates);
                $test->assertCount(1, $booking->guest);
            },
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
            },
            null,
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(BookingDate::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->persist(Argument::type(Guest::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->flush()->shouldHaveBeenCalledOnce();
                $test->assertEquals(400, $booking->totalPrice);
                $test->assertCount(1, $booking->dates);
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
            },
            null,
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(BookingDate::class))->shouldHaveBeenCalledTimes(2);
                $test->entityManager->persist(Argument::type(Guest::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->flush()->shouldHaveBeenCalledOnce();
                $test->assertEquals(700, $booking->totalPrice);
                $test->assertCount(2, $booking->dates);
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
            },
            null,
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(BookingDate::class))->shouldHaveBeenCalledTimes(2);
                $test->entityManager->persist(Argument::type(Guest::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->flush()->shouldHaveBeenCalledOnce();
                $test->assertEquals(700, $booking->totalPrice);
                $test->assertCount(2, $booking->dates);
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
            },
            null,
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(BookingDate::class))->shouldHaveBeenCalledTimes(6);
                $test->entityManager->persist(Argument::type(Guest::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->flush()->shouldHaveBeenCalledOnce();
                $test->assertEquals(1900, $booking->totalPrice);
                $test->assertCount(6, $booking->dates);
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
                $test->entityManager->persist(Argument::type(BookingDate::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->persist(Argument::type(Guest::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->persist(Argument::type(Booking::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->flush()->shouldHaveBeenCalledOnce();
                $test->assertEquals(400, $booking->totalPrice);
                $test->assertCount(1, $booking->dates);
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
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(BookingDate::class))->shouldHaveBeenCalledOnce();
                $test->entityManager->persist(Argument::type(Guest::class))->shouldHaveBeenCalledOnce();
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
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(BookingDate::class))->shouldHaveBeenCalledOnce();
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
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(BookingDate::class))->shouldHaveBeenCalledOnce();
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
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(BookingDate::class))->shouldHaveBeenCalledOnce();
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
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(BookingDate::class))->shouldHaveBeenCalledOnce();
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
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(BookingDate::class))->shouldHaveBeenCalledOnce();
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
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(BookingDate::class))->shouldHaveBeenCalledOnce();
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
                $test->repository->findOneByGoldenId(Argument::any())->willThrow(new BookingNotFoundException());
            },
            BadPriceException::class,
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(BookingDate::class))->shouldHaveBeenCalledTimes(1);
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
            function ($test, $booking) {
                $test->entityManager->persist(Argument::type(BookingDate::class))->shouldHaveBeenCalledTimes(1);
            },
        ];
    }
}
