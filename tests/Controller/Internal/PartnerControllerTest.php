<?php

declare(strict_types=1);

namespace App\Tests\Controller\Internal;

use App\Contract\Request\Internal\Partner\PartnerCreateRequest;
use App\Contract\Request\Internal\Partner\PartnerUpdateRequest;
use App\Contract\Response\Internal\Partner\PartnerGetResponse;
use App\Contract\Response\Internal\Partner\PartnerUpdateResponse;
use App\Controller\Api\PartnerController;
use App\Entity\Partner;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Repository\PartnerNotFoundException;
use App\Manager\PartnerManager;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Controller\Api\PartnerController
 */
class PartnerControllerTest extends TestCase
{
    /**
     * @covers ::get
     * @covers \App\Contract\Response\Internal\Partner\PartnerGetResponse::__construct
     */
    public function testIfGetWillThrowResourceNotFoundException(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $controller = new PartnerController();
        $partnerManager = $this->prophesize(PartnerManager::class);
        $partnerManager->get($uuid)->willThrow(PartnerNotFoundException::class);
        $this->expectException(ResourceNotFoundException::class);
        $controller->get(Uuid::fromString($uuid), $partnerManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\Internal\Partner\PartnerGetResponse::__construct
     */
    public function testGet(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $partner = new Partner();
        $partner->uuid = $uuidInterface->reveal();
        $partner->goldenId = '1234';
        $partner->status = 'alive';
        $partner->currency = 'USD';
        $partner->isChannelManagerActive = true;
        $partner->ceaseDate = new \DateTime();
        $partner->createdAt = new \DateTime();
        $partner->updatedAt = new \DateTime();

        $controller = new PartnerController();
        $partnerManager = $this->prophesize(PartnerManager::class);
        $partnerManager->get($uuid)->willReturn($partner);
        $return = $controller->get(Uuid::fromString($uuid), $partnerManager->reveal());
        $this->assertEquals(PartnerGetResponse::class, get_class($return));
        $this->assertEquals($uuid, $return->uuid);
        $this->assertEquals($partner->goldenId, $return->goldenId);
        $this->assertEquals($partner->status, $return->status);
        $this->assertEquals($partner->currency, $return->currency);
        $this->assertEquals($partner->ceaseDate, $return->ceaseDate);
        $this->assertEquals($partner->createdAt, $return->createdAt);
        $this->assertEquals($partner->updatedAt, $return->updatedAt);
    }

    /**
     * @covers ::put
     */
    public function testIfPutThrowsResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $partnerUpdateRequest = new PartnerUpdateRequest();
        $partnerManager = $this->prophesize(PartnerManager::class);
        $partnerManager->update($uuid, $partnerUpdateRequest)->willThrow(PartnerNotFoundException::class);
        $controller = new PartnerController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->put(Uuid::fromString($uuid), $partnerUpdateRequest, $partnerManager->reveal());
    }

    /**
     * @covers ::put
     * @dataProvider samplePartner
     */
    public function testPut(string $uuid, Partner $partner)
    {
        $partnerUpdateRequest = new PartnerUpdateRequest();
        $partnerManager = $this->prophesize(PartnerManager::class);
        $partnerManager->update($uuid, $partnerUpdateRequest)->shouldBeCalled()->willReturn($partner);
        $controller = new PartnerController();
        $response = $controller->put(Uuid::fromString($uuid), $partnerUpdateRequest, $partnerManager->reveal());
        $this->assertInstanceOf(PartnerUpdateResponse::class, $response);
        $this->assertEquals(200, $response->getHttpCode());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $partnerManager = $this->prophesize(PartnerManager::class);
        $partnerManager->delete($uuid)->willThrow(PartnerNotFoundException::class);
        $controller = new PartnerController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->delete(Uuid::fromString($uuid), $partnerManager->reveal());
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $partnerManager = $this->prophesize(PartnerManager::class);
        $partnerManager->delete($uuid)->shouldBeCalled();
        $controller = new PartnerController();
        $response = $controller->delete(Uuid::fromString($uuid), $partnerManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\Internal\Partner\PartnerCreateResponse::__construct
     * @dataProvider samplePartner
     */
    public function testCreate(string $uuid, Partner $partner)
    {
        $partnerCreateRequest = new PartnerCreateRequest();
        $partnerManager = $this->prophesize(PartnerManager::class);
        $partnerManager->create($partnerCreateRequest)->willReturn($partner);
        $controller = new PartnerController();
        $partnerCreateResponse = $controller->create($partnerCreateRequest, $partnerManager->reveal());

        $this->assertEquals($uuid, $partnerCreateResponse->uuid);
    }

    public function samplePartner(): iterable
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $partner = new Partner();
        $partner->uuid = $uuidInterface->reveal();
        $partner->goldenId = '1234';
        $partner->status = 'active';
        $partner->currency = 'EUR';
        $partner->isChannelManagerActive = true;
        $partner->ceaseDate = null;
        $partner->createdAt = new \DateTime();
        $partner->updatedAt = new \DateTime();

        yield [$uuid, $partner];
    }
}
