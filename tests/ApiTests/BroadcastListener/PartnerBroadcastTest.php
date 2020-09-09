<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\BroadcastListener;

use App\Repository\PartnerRepository;
use App\Tests\ApiTests\IntegrationTestCase;

class PartnerBroadcastTest extends IntegrationTestCase
{
    public function testCreatePartner()
    {
        $payload = [
            'id' => bin2hex(random_bytes(12)),
            'status' => 'partner',
            'currencyCode' => 'EUR',
        ];

        $response = self::$broadcastListenerHelper->testPartners($payload);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume('listener-partner');

        /** @var PartnerRepository $partnerRepository */
        $partnerRepository = self::$container->get(PartnerRepository::class);
        $partner = $partnerRepository->findOneByGoldenId($payload['id']);
        $this->assertEquals($payload['id'], $partner->goldenId);
        $this->assertEquals($payload['status'], $partner->status);
        $this->assertEquals($payload['currencyCode'], $partner->currency);
    }
}
