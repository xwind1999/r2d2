<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Contract\Request\RateBand\RateBandCreateRequest;
use App\Contract\Request\RateBand\RateBandUpdateRequest;
use App\Contract\Response\RateBand\RateBandGetResponse;
use App\Controller\Api\RateBandController;
use App\Entity\RateBand;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\RateBandManager;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Controller\Api\PartnerController
 */
class RateBandControllerTest extends TestCase
{
    /**
     * @covers ::get
     * @covers \App\Contract\Response\RateBand\RateBandGetResponse::__construct
     */
    public function testIfGetWillThrowResourceNotFoundException(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $controller = new RateBandController();
        $rateBandManager = $this->prophesize(RateBandManager::class);
        $rateBandManager->get($uuid)->willThrow(EntityNotFoundException::class);
        $this->expectException(ResourceNotFoundException::class);
        $controller->get($uuid, $rateBandManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\RateBand\RateBandGetResponse::__construct
     */
    public function testIfGetWillThrowUnprocessableEntityException(): void
    {
        $controller = new RateBandController();
        $rateBandManager = $this->prophesize(RateBandManager::class);
        $this->expectException(UnprocessableEntityException::class);
        $controller->get('12345', $rateBandManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\RateBand\RateBandGetResponse::__construct
     */
    public function testGet(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $rateBand = new RateBand();
        $rateBand->uuid = $uuidInterface->reveal();
        $rateBand->goldenId = '1234';
        $rateBand->partnerGoldenId = '4321';
        $rateBand->name = 'rateBandName';
        $rateBand->createdAt = new \DateTime();
        $rateBand->updatedAt = new \DateTime();

        $controller = new RateBandController();
        $rateBandManager = $this->prophesize(RateBandManager::class);
        $rateBandManager->get($uuid)->willReturn($rateBand);
        $return = $controller->get($uuid, $rateBandManager->reveal());
        $this->assertEquals(RateBandGetResponse::class, get_class($return));
        $this->assertEquals($uuid, $return->uuid);
        $this->assertEquals($rateBand->goldenId, $return->goldenId);
        $this->assertEquals($rateBand->partnerGoldenId, $return->partnerGoldenId);
        $this->assertEquals($rateBand->name, $return->name);
        $this->assertEquals($rateBand->createdAt, $return->createdAt);
        $this->assertEquals($rateBand->updatedAt, $return->updatedAt);
    }

    /**
     * @covers ::put
     */
    public function testIfPutThrowsResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $rateBandUpdateRequest = new RateBandUpdateRequest();
        $rateBandManager = $this->prophesize(RateBandManager::class);
        $rateBandManager->update($uuid, $rateBandUpdateRequest)->willThrow(EntityNotFoundException::class);
        $controller = new RateBandController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->put($uuid, $rateBandUpdateRequest, $rateBandManager->reveal());
    }

    /**
     * @covers ::put
     */
    public function testPut()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $rateBandUpdateRequest = new RateBandUpdateRequest();
        $rateBandManager = $this->prophesize(RateBandManager::class);
        $rateBandManager->update($uuid, $rateBandUpdateRequest)->shouldBeCalled();
        $controller = new RateBandController();
        $response = $controller->put($uuid, $rateBandUpdateRequest, $rateBandManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowUnprocessableEntityException()
    {
        $rateBandManager = $this->prophesize(RateBandManager::class);
        $controller = new RateBandController();
        $this->expectException(UnprocessableEntityException::class);
        $controller->delete('1234', $rateBandManager->reveal());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $rateBandManager = $this->prophesize(RateBandManager::class);
        $rateBandManager->delete($uuid)->willThrow(EntityNotFoundException::class);
        $controller = new RateBandController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->delete($uuid, $rateBandManager->reveal());
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $rateBandManager = $this->prophesize(RateBandManager::class);
        $rateBandManager->delete($uuid)->shouldBeCalled();
        $controller = new RateBandController();
        $response = $controller->delete($uuid, $rateBandManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\RateBand\RateBandCreateResponse::__construct
     */
    public function testCreate()
    {
        $rateBandCreateRequest = new RateBandCreateRequest();
        $uuid = '1234';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $rateBand = new RateBand();
        $rateBand->uuid = $uuidInterface->reveal();
        $rateBandManager = $this->prophesize(RateBandManager::class);
        $rateBandManager->create($rateBandCreateRequest)->willReturn($rateBand);
        $controller = new RateBandController();
        $rateBandCreateResponse = $controller->create($rateBandCreateRequest, $rateBandManager->reveal());

        $this->assertEquals($uuid, $rateBandCreateResponse->uuid);
    }
}
