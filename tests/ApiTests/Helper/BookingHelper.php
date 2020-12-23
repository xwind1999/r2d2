<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\Helper;

use App\Constants\DateTimeConstants;
use App\Tests\ApiTests\DBValidationTests\Booking\BookingIntegrationTest;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

class BookingHelper
{
    const API_BASE_URL = '/booking';
    protected AbstractBrowser $client;
    protected Serializer $serializer;
    protected ?string $baseUrl = null;

    public function __construct(AbstractBrowser $client, Serializer $serializer, ?string $baseUrl)
    {
        $this->client = $client;
        $this->baseUrl = $baseUrl;
        $this->serializer = $serializer;
    }

    /**
     * @return JsonResponse|object
     */
    public function create(array $payload)
    {
        return $this->request('POST', $this->baseUrl.self::API_BASE_URL, $payload);
    }

    /**
     * @return JsonResponse|object
     */
    public function update(array $payload)
    {
        return $this->request('PATCH', $this->baseUrl.self::API_BASE_URL, $payload);
    }

    public function request(string $method, string $url, array $body)
    {
        $this->client->request($method, $url, [], [], [], $this->serializer->serialize($body, 'json'));

        return $this->client->getResponse();
    }

    public function defaultPayload(array $overrides = []): array
    {
        $this->startDate = isset($overrides['startDate']) ?
            new \DateTime($overrides['startDate']) :
            new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month')));

        return $overrides +
            [
                'bookingId' => bin2hex(random_bytes(8)),
                'box' => BookingIntegrationTest::BOX_GOLDEN_ID,
                'experience' => [
                    'id' => BookingIntegrationTest::EXPERIENCE_GOLDEN_ID,
                    'components' => [
                        'Three night stay',
                    ],
                ],
                'currency' => 'EUR',
                'voucher' => '198257918',
                'startDate' => (clone $this->startDate)->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                'endDate' => (clone $this->startDate)->modify('+1 day')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                'customerComment' => 'Clean sheets please',
                'guests' => [
                    [
                        'firstName' => 'Hermano',
                        'lastName' => 'Guido',
                        'email' => 'maradona@worldcup.ar',
                        'phone' => '123 123 123',
                    ],
                ],
                'rooms' => [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => (clone $this->startDate)->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                        ],
                    ],
                ],
            ];
    }

    public function cleanUpBooking(array $componentGoldenIdList, array $experienceGoldenIdList, EntityManager $entityManager): void
    {
        $entityManager->getConnection()->executeStatement('SET foreign_key_checks = 0');

        $entityManager->getConnection()
            ->executeStatement("
                DELETE FROM r2d2.booking_date 
                    WHERE component_golden_id IN ('".implode("','", $componentGoldenIdList)."')"
            );

        $entityManager->getConnection()
            ->executeStatement("
                DELETE FROM r2d2.booking 
                    WHERE experience_golden_id IN ('".implode("','", $experienceGoldenIdList)."')");

        $entityManager->getConnection()->executeStatement('SET foreign_key_checks = 1');
    }
}
