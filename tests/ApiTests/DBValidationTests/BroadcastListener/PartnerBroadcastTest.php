<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\DBValidationTests;

use App\Constants\DateTimeConstants;
use App\Entity\Partner;
use App\Repository\PartnerRepository;
use App\Tests\ApiTests\IntegrationTestCase;
use Symfony\Component\HttpFoundation\Response;

class PartnerBroadcastTest extends IntegrationTestCase
{
    private static $partnerGoldenId;
    private static $partnerCeaseDate;

    public static function setUpBeforeClass(): void
    {
        static::$partnerGoldenId = bin2hex(random_bytes(12));
        static::$partnerCeaseDate = (new \DateTime())->format(DateTimeConstants::DATE_TIME_MILLISECONDS);

        parent::setUpBeforeClass();
    }

    public function testCreatePartner()
    {
        $payload = [
            'id' => static::$partnerGoldenId,
            'status' => 'partner',
            'currencyCode' => 'EUR',
        ];

        $response = self::$broadcastListenerHelper->testPartners($payload);
        self::assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());

        $this->consume('listener-partner');

        $partnerRepository = self::$container->get(PartnerRepository::class);
        $partner = $partnerRepository->findOneByGoldenId($payload['id']);
        self::assertEquals(static::$partnerGoldenId, $partner->goldenId);
        self::assertEquals($payload['status'], $partner->status);
        self::assertEquals($payload['currencyCode'], $partner->currency);
    }

    /**
     * @depends testCreatePartner
     */
    public function testUpdatePartnerWithCeaseDateWithEaiTimestamp()
    {
        $payload = [
            'id' => static::$partnerGoldenId,
            'status' => 'ceased',
            'currencyCode' => 'EUR',
            'partnerCeaseDate' => static::$partnerCeaseDate,
        ];

        $date = new \DateTime();
        $header = [
            'HTTP_x-eai-timestamp' => (string) $date->getTimestamp(),
        ];

        $response = self::$broadcastListenerHelper->testPartners($payload, $header);
        self::assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());

        $this->consume('listener-partner');

        $partnerRepository = self::$container->get(PartnerRepository::class);
        /** @var Partner $partner */
        $partner = $partnerRepository->findOneByGoldenId($payload['id']);
        self::assertEquals(static::$partnerGoldenId, $partner->goldenId);
        self::assertEquals($payload['status'], $partner->status);
        self::assertEquals($payload['currencyCode'], $partner->currency);
        self::assertEquals(
            (new \DateTime($payload['partnerCeaseDate']))->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
            $partner->ceaseDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
        );
    }

    /**
     * @depends testUpdatePartnerWithCeaseDateWithEaiTimestamp
     */
    public function testUpdatePartnerWithoutEaiTimestamp()
    {
        $payload = [
            'id' => static::$partnerGoldenId,
            'status' => 'partner',
            'currencyCode' => 'EUR',
        ];

        $response = self::$broadcastListenerHelper->testPartners($payload);
        self::assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());

        try {
            $this->consume('listener-partner');
        } catch (\Exception $exception) {
        }

        $partnerRepository = self::$container->get(PartnerRepository::class);
        /** @var Partner $partner */
        $partner = $partnerRepository->findOneByGoldenId($payload['id']);
        self::assertEquals(static::$partnerGoldenId, $partner->goldenId);
        self::assertEquals('ceased', $partner->status);
        self::assertEquals($payload['currencyCode'], $partner->currency);
        self::assertEquals(
            (new \DateTime(static::$partnerCeaseDate))->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
            $partner->ceaseDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
        );
    }
}
