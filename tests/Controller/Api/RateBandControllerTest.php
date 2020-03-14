<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Contract\Request\RateBand\RateBandCreateRequest;
use App\Contract\Request\RateBand\RateBandUpdateRequest;
use App\Contract\Response\RateBand\RateBandGetResponse;
use App\Contract\Response\RateBand\RateBandUpdateResponse;
use App\Controller\Api\RateBandController;
use App\Entity\RateBand;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Repository\RateBandNotFoundException;
use App\Manager\RateBandManager;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Controller\Api\RateBandController
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
        $rateBandManager->get($uuid)->willThrow(RateBandNotFoundException::class);
        $this->expectException(ResourceNotFoundException::class);
        $controller->get(Uuid::fromString($uuid), $rateBandManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\RateBand\RateBandGetResponse::__construct
     * @dataProvider sampleRateBand
     */
    public function testGet(string $uuid, RateBand $rateBand): void
    {
        $controller = new RateBandController();
        $rateBandManager = $this->prophesize(RateBandManager::class);
        $rateBandManager->get($uuid)->willReturn($rateBand);
        $return = $controller->get(Uuid::fromString($uuid), $rateBandManager->reveal());
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
        $rateBandManager->update($uuid, $rateBandUpdateRequest)->willThrow(RateBandNotFoundException::class);
        $controller = new RateBandController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->put(Uuid::fromString($uuid), $rateBandUpdateRequest, $rateBandManager->reveal());
    }

    /**
     * @covers ::put
     * @dataProvider sampleRateBand
     */
    public function testPut(string $uuid, RateBand $rateBand)
    {
        $rateBandUpdateRequest = new RateBandUpdateRequest();
        $rateBandManager = $this->prophesize(RateBandManager::class);
        $rateBandManager->update($uuid, $rateBandUpdateRequest)->shouldBeCalled()->willReturn($rateBand);
        $controller = new RateBandController();
        $response = $controller->put(Uuid::fromString($uuid), $rateBandUpdateRequest, $rateBandManager->reveal());
        $this->assertInstanceOf(RateBandUpdateResponse::class, $response);
        $this->assertEquals(200, $response->getHttpCode());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $rateBandManager = $this->prophesize(RateBandManager::class);
        $rateBandManager->delete($uuid)->willThrow(RateBandNotFoundException::class);
        $controller = new RateBandController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->delete(Uuid::fromString($uuid), $rateBandManager->reveal());
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
        $response = $controller->delete(Uuid::fromString($uuid), $rateBandManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\RateBand\RateBandCreateResponse::__construct
     * @dataProvider sampleRateBand
     */
    public function testCreate(string $uuid, RateBand $rateBand)
    {
        $rateBandCreateRequest = new RateBandCreateRequest();
        $rateBandManager = $this->prophesize(RateBandManager::class);
        $rateBandManager->create($rateBandCreateRequest)->willReturn($rateBand);
        $controller = new RateBandController();
        $rateBandCreateResponse = $controller->create($rateBandCreateRequest, $rateBandManager->reveal());

        $this->assertEquals($uuid, $rateBandCreateResponse->uuid);
    }

    public function sampleRateBand(): iterable
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $rateBand = new RateBand();
        $rateBand->uuid = $uuidInterface->reveal();
        $rateBand->goldenId = '1234';
        $rateBand->partnerGoldenId = 'partner1234';
        $rateBand->name = 'test rate band';
        $rateBand->createdAt = new \DateTime();
        $rateBand->updatedAt = new \DateTime();

        yield [$uuid, $rateBand];
    }
}
