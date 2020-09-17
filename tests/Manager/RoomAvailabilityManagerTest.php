<?php

declare(strict_types=1);

namespace App\Tests\Manager;

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
use App\Event\Product\AvailabilityUpdatedEvent;
use App\Exception\Manager\RoomAvailability\InvalidRoomStockTypeException;
use App\Exception\Manager\RoomAvailability\OutdatedRoomAvailabilityInformationException;
use App\Exception\Repository\ComponentNotFoundException;
use App\Manager\RoomAvailabilityManager;
use App\Repository\ComponentRepository;
use App\Repository\RoomAvailabilityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

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
     * @covers ::getRoomAvailabilitiesByMultipleComponentGoldenIds
     */
    public function testGetRoomAvailabilitiesByComponentGoldenIds()
    {
        $compIds = [
            '1234', '4321', '1111',
        ];
        $this->repository->findRoomAvailabilitiesByMultipleComponentGoldenIds(
            Argument::any(),
            new \DateTime('2020-06-20'),
            new \DateTime('2020-06-30')
        )->willReturn($compIds);
        $this->manager->getRoomAvailabilitiesByMultipleComponentGoldenIds(
            $compIds,
            new \DateTime('2020-06-20'),
            new \DateTime('2020-06-30')
        );

        $this->repository->findRoomAvailabilitiesByMultipleComponentGoldenIds(
            $compIds,
            new \DateTime('2020-06-20'),
            new \DateTime('2020-06-30')
        )->shouldBeCalledOnce();
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAvailabilitiesByExperienceId
     */
    public function testGetRoomAvailabilitiesByExperienceId()
    {
        $roomAvais = [
            '1234', '4321', '1111',
        ];
        $this->repository->findAvailableRoomsByExperienceId(
            '1234',
            new \DateTime('2020-06-20'),
            new \DateTime('2020-06-30')
        )->willReturn($roomAvais);
        $this->manager->getRoomAvailabilitiesByExperienceId(
            '1234',
            new \DateTime('2020-06-20'),
            new \DateTime('2020-06-30')
        );

        $this->repository->findAvailableRoomsByExperienceId(
            '1234',
            new \DateTime('2020-06-20'),
            new \DateTime('2020-06-30')
        )->shouldBeCalledOnce();
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAvailabilitiesByComponent
     */
    public function testGetRoomAvailabilitiesListByComponentGoldenId()
    {
        $component = new Component();
        $this->repository->findRoomAvailabilitiesByComponent($component, Argument::any(), Argument::any())
            ->willReturn(
                [
                    0 => [
                        'stock' => 10,
                        'date' => '2020-07-20',
                        'type' => 'instant',
                    ],
                ]
            );
        $this->manager->getRoomAvailabilitiesByComponent($component, new \DateTime('2020-06-20'), new \DateTime('2020-06-30'));

        $this->repository->findRoomAvailabilitiesByComponent($component, new \DateTime('2020-06-20'), new \DateTime('2020-06-30'))
            ->shouldBeCalledOnce();
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
        $this
            ->repository
            ->findAvailableRoomsByMultipleExperienceIds($experienceGoldenIds, $startDate)
            ->shouldBeCalled()
            ->willReturn(['results']);

        $this->assertEquals(
            ['results'],
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
        $this->repository->findAvailableRoomsByBoxId($boxId, Argument::any(), Argument::any())
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
     * @covers ::createDatePeriod
     * @covers ::hasAvailabilityChangedForBoxCache
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

        $this
            ->eventDispatcher
            ->dispatch(Argument::type(AvailabilityUpdatedEvent::class))
            ->willReturn(new \stdClass());

        $this->assertNull($this->manager->replace($roomAvailabilityRequest));
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
            $date->format('Y-m-d') => $roomAvailabilityExistent,
            $date2->format('Y-m-d') => $roomAvailabilityExistent2,
            $date3->format('Y-m-d') => $roomAvailabilityExistent3,
            $date4->format('Y-m-d') => $roomAvailabilityExistent4,
        ];

        yield 'room-availability-update-with-no-meaningful-change' => [
            $component,
            (function ($test, $roomAvailabilityList, $component) {
                $test->repository->findByComponentAndDateRange(Argument::cetera())->willReturn($roomAvailabilityList);

                $test->em->flush()->shouldBeCalledTimes(1);
                $test->em->persist(Argument::type(RoomAvailability::class))->shouldBeCalledTimes(4);
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

        yield 'room-availability-update-request-with-meaningful-changes' => [
            $component,
            (function ($test, $roomAvailabilityList, $component) {
                $test->repository->findByComponentAndDateRange(Argument::cetera())->willReturn($roomAvailabilityList);
                $test->em->flush()->shouldBeCalledTimes(1);
                $test->em->persist(Argument::type(RoomAvailability::class))->shouldBeCalledTimes(4);
                $test->componentRepository->findOneByGoldenId(Argument::any())->willReturn($component);
            }),
            (function ($roomAvailabilityRequest) {
                //zeroing the stock, so we may need to recalculate some stuff
                $roomAvailabilityRequest->quantity = 0;
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
                $test->em->flush()->shouldBeCalledTimes(1);
                $test->em->persist(Argument::type(RoomAvailability::class))->shouldBeCalledTimes(4);
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
                $test->em->flush()->shouldNotBeCalled();
                $test->em->persist()->shouldNotBeCalled();
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
                $test->em->flush()->shouldNotBeCalled();
                $test->em->persist()->shouldNotBeCalled();
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
                $test->em->flush()->shouldBeCalledTimes(1);
                $test->em->persist(Argument::type(RoomAvailability::class))->shouldBeCalledTimes($diffDate);
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
                $test->em->flush()->shouldBeCalledTimes(1);
                $test->em->persist(Argument::type(RoomAvailability::class))->shouldNotBeCalled();
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
                    $roomAvailability->externalUpdatedAt = (clone $roomAvailability->externalUpdatedAt)->modify('-1 week');
                }

                return $roomAvailabilityList;
            })($roomAvailabilityList),
            null,
        ];
    }

    /**
     * @dataProvider bookingProvider
     */
    public function testUpdateStockBookingConfirmation(Booking $booking, array $availability, callable $prophecies)
    {
        $prophecies($this, $availability);

        $response = $this->manager->updateStockBookingConfirmation($booking);
        $this->assertNull($response);
    }

    public function bookingProvider()
    {
        $booking = new Booking();
        $booking->voucher = '198257918';
        $booking->goldenId = '12345';
        $dateTime = new \DateTime('2020-10-01');
        $booking->startDate = $dateTime;
        $booking->endDate = (new $dateTime())->modify('+1 day');
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
        $component = $this->prophesize(Component::class);
        $component->goldenId = '5464';
        $component->name = 'component name';
        $experienceComponent->component = $component->reveal();

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

        $bookingDate = $this->prophesize(BookingDate::class);
        $bookingDate->componentGoldenId = '5464';
        $bookingDate->date = $dateTime;
        $bookingDate->price = 1212;
        $booking->bookingDate = new ArrayCollection([$bookingDate->reveal()]);

        $guest = $this->prophesize(Guest::class);
        $guest->firstName = 'First Name';
        $guest->lastName = 'Last Name';
        $guest->phone = '089 585 5555';
        $guest->email = 'teste@teste.com';
        $booking->guest = new ArrayCollection([$guest->reveal()]);

        $availability = [
            [
                'componentGoldenId' => '11111',
                'date' => $dateTime->format('Y-m-d'),
                'stock' => 10,
            ],
        ];

        yield 'update-booking-with-success' => [
            $booking,
            $availability,
            (function ($test, $availability) {
                $availability[0]['stock'] = 7;
                $test->repository
                    ->getAvailabilityByBookingAndDates(Argument::type(Booking::class))
                    ->willReturn($availability);
                $test->repository
                    ->updateStockByComponentAndDates(Argument::type('string'), Argument::type(\DateTime::class))
                    ->shouldBeCalledOnce();
                $test->repository
                    ->updateStockByComponentAndDates(Argument::type('string'), Argument::type(\DateTime::class))
                    ->willReturn(1);
            }),
        ];

        yield 'update-bookings-big-range' => [
            (function ($booking) {
                $booking->endDate = (new $booking->startDate())->modify('+10 day');

                return $booking;
            })(clone $booking),
            $availability,
            (function ($test, $availability) {
                $test->repository
                    ->getAvailabilityByBookingAndDates(Argument::type(Booking::class))
                    ->willReturn($availability);
                $test->repository
                    ->updateStockByComponentAndDates(Argument::type('string'), Argument::type(\DateTime::class))
                    ->shouldBeCalledOnce();
                $test->repository
                    ->updateStockByComponentAndDates(Argument::type('string'), Argument::type(\DateTime::class))
                    ->willReturn(1);
            }),
        ];
    }
}
