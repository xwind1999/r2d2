<?php

declare(strict_types=1);

namespace App\Tests\Provider;

use App\CMHub\CMHub;
use App\Contract\Response\CMHub\CMHubErrorResponse;
use App\Contract\Response\CMHub\CMHubResponse;
use App\Contract\Response\CMHub\GetAvailabilityResponse;
use App\Manager\ComponentManager;
use App\Manager\ExperienceManager;
use App\Manager\RoomAvailabilityManager;
use App\Manager\RoomPriceManager;
use App\Provider\AvailabilityProvider;
use App\Repository\BookingDateRepository;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @coversDefaultClass \App\Provider\AvailabilityProvider
 * @group availability-provider
 */
class AvailabilityProviderTest extends TestCase
{
    /**
     * @var CMHub|ObjectProphecy
     */
    protected $cmHub;

    /**
     * @var ObjectProphecy|SerializerInterface
     */
    protected $serializer;

    /**
     * @var ArrayTransformerInterface|ObjectProphecy
     */
    private $arraySerializer;

    /**
     * @var ExperienceManager|ObjectProphecy
     */
    protected $experienceManager;

    /**
     * @var ComponentManager|ObjectProphecy
     */
    protected $componentManager;

    /**
     * @var ObjectProphecy|RoomAvailabilityManager
     */
    protected $roomAvailabilityManager;

    /**
     * @var ObjectProphecy|RoomPriceManager
     */
    protected $roomPriceManager;

    private AvailabilityProvider $availabilityProvider;

    /**
     * @var BookingDateRepository|ObjectProphecy
     */
    private ObjectProphecy $bookingDateRepository;

    public function setUp(): void
    {
        $this->cmHub = $this->prophesize(CMHub::class);
        $this->serializer = $this->prophesize(SerializerInterface::class);
        $this->arraySerializer = $this->prophesize(ArrayTransformerInterface::class);
        $this->experienceManager = $this->prophesize(ExperienceManager::class);
        $this->componentManager = $this->prophesize(ComponentManager::class);
        $this->roomAvailabilityManager = $this->prophesize(RoomAvailabilityManager::class);
        $this->roomPriceManager = $this->prophesize(RoomPriceManager::class);
        $this->bookingDateRepository = $this->prophesize(BookingDateRepository::class);
        $this->availabilityProvider = new AvailabilityProvider(
            $this->cmHub->reveal(),
            $this->serializer->reveal(),
            $this->arraySerializer->reveal(),
            $this->experienceManager->reveal(),
            $this->componentManager->reveal(),
            $this->roomAvailabilityManager->reveal(),
            $this->roomPriceManager->reveal(),
            $this->bookingDateRepository->reveal()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailability
     * @covers \App\Contract\Response\CMHub\GetAvailability\AvailabilityResponse
     */
    public function testGetAvailability()
    {
        $productId = 286201;
        $dateFrom = new \DateTime('2020-04-04');
        $dateTo = new \DateTime('2020-04-04');

        $response = $this->prophesize(ResponseInterface::class);
        $this->cmHub->getAvailability($productId, $dateFrom, $dateTo)->willReturn($response->reveal());

        $argument = '[{"date":"2020-04-28","quantity":40},{"date":"2020-04-29","quantity":40}]';
        $response->getContent()->shouldBeCalled()->willReturn($argument);
        $this->serializer->deserialize(
            $argument,
            sprintf('array<%s>', GetAvailabilityResponse::class), 'json')
            ->willReturn(
                [
                    [
                        'date' => '2020-04-28',
                        'quantity' => 40,
                    ],
            ]
            )->shouldBeCalled();

        $response = $this->availabilityProvider->getAvailability($productId, $dateFrom, $dateTo);

        $this->assertInstanceOf(CMHubResponse::class, $response);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailability
     * @covers \App\Contract\Response\CMHub\CMHubErrorResponse
     */
    public function testGetAvailabilityThrowsHttpException()
    {
        $productId = 286201;
        $dateFrom = new \DateTime('2020-04-04');
        $dateTo = new \DateTime('2020-04-04');

        $result = $this->prophesize(CMHubErrorResponse::class);
        $responseInterface = $this->prophesize(ResponseInterface::class);
        $exception = $this->prophesize(HttpExceptionInterface::class);

        $this->cmHub->getAvailability($productId, $dateFrom, $dateTo)->willThrow($exception->reveal());
        $exception->getResponse()->willReturn($responseInterface->reveal());
        $responseInterface->toArray(false)->willReturn(['error' => ['code' => 404, 'message' => 'Not Found']]);

        $this->arraySerializer
            ->fromArray(['code' => 404, 'message' => 'Not Found'], CMHubErrorResponse::class)
            ->willReturn($result->reveal())
        ;

        $this->assertInstanceOf(
            CMHubErrorResponse::class,
            $this->availabilityProvider->getAvailability($productId, $dateFrom, $dateTo))
        ;
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAvailabilitiesByBoxIdAndStartDate
     */
    public function testGetRoomAvailabilitiesByBoxId()
    {
        $boxId = '1234';
        $dateFrom = new \DateTime('2020-06-20');

        $expectedArray = [
            [
                'Package' => '1234',
                'Stock' => 3,
                'Request' => 0,
            ],
            [
                'Package' => '1235',
                'Stock' => 0,
                'Request' => 3,
            ],
            [
                'Package' => '1236',
                'Stock' => 2,
                'Request' => 1,
            ],
        ];

        $this->roomAvailabilityManager->getRoomAvailabilitiesByBoxId(
            $boxId,
            Argument::any(),
        )->willReturn($expectedArray);

        $this->assertEquals(
            $expectedArray,
            $this->availabilityProvider->getRoomAvailabilitiesByBoxIdAndStartDate($boxId, $dateFrom)
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAndPricesAvailabilitiesByExperienceIdAndDates
     */
    public function testGetRoomAndPriceAvailabilitiesByExperienceIdAndDates()
    {
        $expId = '1234';
        $dateFrom = new \DateTime('2020-06-20');
        $dateTo = new \DateTime('2020-06-23');

        $expectedArray = [
            [
                'stock' => '1',
                'experienceGoldenId' => '1234',
                'duration' => '1',
                'date' => '2020-06-20',
                'partnerGoldenId' => '4321',
                'isSellable' => '1',
                'roomStockType' => 'stock',
            ],
            [
                'stock' => '1',
                'experienceGoldenId' => '1234',
                'duration' => '1',
                'date' => '2020-06-21',
                'partnerGoldenId' => '4321',
                'isSellable' => '1',
                'roomStockType' => 'stock',
            ],
            [
                'stock' => '1',
                'experienceGoldenId' => '1234',
                'duration' => '1',
                'date' => '2020-06-22',
                'partnerGoldenId' => '4321',
                'isSellable' => '1',
                'roomStockType' => 'stock',
            ],
            [
                'stock' => '1',
                'experienceGoldenId' => '1234',
                'duration' => '1',
                'date' => '2020-06-23',
                'partnerGoldenId' => '4321',
                'isSellable' => '1',
                'roomStockType' => 'stock',
            ],
        ];

        $this->roomAvailabilityManager->getRoomAndPriceAvailabilitiesByExperienceIdAndDates(
            $expId,
            Argument::any(),
            Argument::any(),
            )->willReturn($expectedArray);

        $this->assertEquals(
            $expectedArray,
            $this->availabilityProvider->getRoomAndPricesAvailabilitiesByExperienceIdAndDates($expId, $dateFrom, $dateTo)
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAndPricesAvailabilitiesByExperienceIdAndDates
     */
    public function testGetRoomAndPriceAvailabilitiesListByExperienceIdAndDates()
    {
        $dateFrom = new \DateTime('2020-06-20');
        $dateTo = new \DateTime('2020-06-26');

        $this->roomAvailabilityManager->getRoomAndPriceAvailabilitiesByExperienceIdAndDates(
            Argument::any(),
            Argument::any(),
            Argument::any()
            )->shouldBeCalledOnce()
            ->willReturn(
                [
                    0 => [
                        'Date' => '2020-06-20T00:00:00.000000',
                        'AvailabilityValue' => 1,
                        'AvailabilityStatus' => 'Available',
                        'SellingPrice' => 86.45,
                        'BuyingPrice' => 86.45,
                    ],
                    1 => [
                        'Date' => '2020-06-21T00:00:00.000000',
                        'AvailabilityValue' => 1,
                        'AvailabilityStatus' => 'Available',
                        'SellingPrice' => 86.45,
                        'BuyingPrice' => 86.45,
                    ],
                    2 => [
                       'Date' => '2020-06-22T00:00:00.000000',
                       'AvailabilityValue' => 1,
                       'AvailabilityStatus' => 'Available',
                       'SellingPrice' => 86.45,
                       'BuyingPrice' => 86.45,
                    ],
                    3 => [
                        'Date' => '2020-06-23T00:00:00.000000',
                        'AvailabilityValue' => 1,
                        'AvailabilityStatus' => 'Available',
                        'SellingPrice' => 86.45,
                        'BuyingPrice' => 86.45,
                    ],
                ]
            );

        $this->availabilityProvider->getRoomAndPricesAvailabilitiesByExperienceIdAndDates(
            '1234',
            $dateFrom,
            $dateTo
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAvailabilitiesByExperienceIdsList
     */
    public function testGetRoomAvailabilitiesByExperienceIdList()
    {
        $startDate = new \DateTime('2020-06-20');

        $experienceIds = ['1234', '4321'];
        $this->roomAvailabilityManager->getRoomAvailabilitiesByMultipleExperienceGoldenIds(
            $experienceIds,
            $startDate
        )->willReturn(
            [
                [
                    'experience_golden_id' => '1234',
                    'duration' => '1',
                    'partner_golden_id' => '00112233',
                    'is_sellable' => '1',
                    'date' => new \DateTime('2020-06-20'),
                    'stock' => 10,
                ],
            ]
        );

        $this->bookingDateRepository->findBookingDatesByExperiencesAndDate(
            Argument::type('array'),
            Argument::type(\DateTime::class)
            )->willReturn([
                [
                    'experience_golden_id' => '1234',
                    'componentGoldenId' => '1111',
                    'date' => new \DateTime('2020-06-20'),
                    'usedStock' => 1,
                ],
            ]);

        $expectedArray = [
            '1234' => [
                'duration' => '1',
                'isSellable' => '1',
                'partnerId' => '00112233',
                'experienceId' => '1234',
            ],
        ];

        $this->assertEquals(
            $expectedArray,
            $this->availabilityProvider->getRoomAvailabilitiesByExperienceIdsList($experienceIds, $startDate)
        );
    }
}
