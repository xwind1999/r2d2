<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Contract\Request\Partner\PartnerCreateRequest;
use App\Contract\Request\Partner\PartnerUpdateRequest;
use App\Contract\Response\Partner\PartnerGetResponse;
use App\Controller\Api\PartnerController;
use App\Entity\Partner;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\PartnerManager;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Controller\Api\PartnerController
 */
class PartnerControllerTest extends TestCase
{
    /**
     * @covers ::get
     * @covers \App\Contract\Response\Partner\PartnerGetResponse::__construct
     */
    public function testIfGetWillThrowResourceNotFoundException(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $controller = new PartnerController();
        $partnerManager = $this->prophesize(PartnerManager::class);
        $partnerManager->get($uuid)->willThrow(EntityNotFoundException::class);
        $this->expectException(ResourceNotFoundException::class);
        $controller->get($uuid, $partnerManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\Partner\PartnerGetResponse::__construct
     */
    public function testIfGetWillThrowUnprocessableEntityException(): void
    {
        $controller = new PartnerController();
        $partnerManager = $this->prophesize(PartnerManager::class);
        $this->expectException(UnprocessableEntityException::class);
        $controller->get('12345', $partnerManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\Partner\PartnerGetResponse::__construct
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
        $partner->ceaseDate = new \DateTime();
        $partner->createdAt = new \DateTime();
        $partner->updatedAt = new \DateTime();

        $controller = new PartnerController();
        $partnerManager = $this->prophesize(PartnerManager::class);
        $partnerManager->get($uuid)->willReturn($partner);
        $return = $controller->get($uuid, $partnerManager->reveal());
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
        $partnerManager->update($uuid, $partnerUpdateRequest)->willThrow(EntityNotFoundException::class);
        $controller = new PartnerController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->put($uuid, $partnerUpdateRequest, $partnerManager->reveal());
    }

    /**
     * @covers ::put
     */
    public function testPut()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $partnerUpdateRequest = new PartnerUpdateRequest();
        $partnerManager = $this->prophesize(PartnerManager::class);
        $partnerManager->update($uuid, $partnerUpdateRequest)->shouldBeCalled();
        $controller = new PartnerController();
        $response = $controller->put($uuid, $partnerUpdateRequest, $partnerManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowUnprocessableEntityException()
    {
        $partnerManager = $this->prophesize(PartnerManager::class);
        $controller = new PartnerController();
        $this->expectException(UnprocessableEntityException::class);
        $controller->delete('1234', $partnerManager->reveal());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $partnerManager = $this->prophesize(PartnerManager::class);
        $partnerManager->delete($uuid)->willThrow(EntityNotFoundException::class);
        $controller = new PartnerController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->delete($uuid, $partnerManager->reveal());
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
        $response = $controller->delete($uuid, $partnerManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\Experience\ExperienceCreateResponse::__construct
     */
    public function testCreate()
    {
        $partnerCreateRequest = new PartnerCreateRequest();
        $uuid = '1234';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $partner = new Partner();
        $partner->uuid = $uuidInterface->reveal();
        $partnerManager = $this->prophesize(PartnerManager::class);
        $partnerManager->create($partnerCreateRequest)->willReturn($partner);
        $controller = new PartnerController();
        $partnerCreateResponse = $controller->create($partnerCreateRequest, $partnerManager->reveal());

        $this->assertEquals($uuid, $partnerCreateResponse->uuid);
    }
}
