<?php

declare(strict_types=1);

namespace App\Tests\Controller\Internal;

use App\Contract\Request\Internal\Box\BoxCreateRequest;
use App\Contract\Request\Internal\Box\BoxUpdateRequest;
use App\Contract\Response\Internal\Box\BoxGetResponse;
use App\Contract\Response\Internal\Box\BoxUpdateResponse;
use App\Controller\Internal\BoxController;
use App\Entity\Box;
use App\Exception\Http\ResourceConflictException;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Repository\BoxNotFoundException;
use App\Manager\BoxManager;
use Doctrine\DBAL\Driver\DriverException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Controller\Internal\BoxController
 */
class BoxControllerTest extends TestCase
{
    /**
     * @covers ::get
     * @covers \App\Contract\Response\Internal\Box\BoxGetResponse::__construct
     */
    public function testIfGetWillThrowResourceNotFoundException(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $controller = new BoxController();
        $boxManager = $this->prophesize(BoxManager::class);
        $boxManager->get($uuid)->willThrow(BoxNotFoundException::class);
        $this->expectException(ResourceNotFoundException::class);
        $controller->get(Uuid::fromString($uuid), $boxManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\Internal\Box\BoxGetResponse::__construct
     */
    public function testGet(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $box = new Box();
        $box->uuid = $uuidInterface->reveal();
        $box->goldenId = '1234';
        $box->status = 'integrated';
        $box->brand = 'sbx';
        $box->country = 'fr';
        $box->createdAt = new \DateTime();
        $box->updatedAt = new \DateTime();

        $controller = new BoxController();
        $boxManager = $this->prophesize(BoxManager::class);
        $boxManager->get($uuid)->willReturn($box);
        $return = $controller->get(Uuid::fromString($uuid), $boxManager->reveal());
        $this->assertEquals(BoxGetResponse::class, get_class($return));
        $this->assertEquals($uuid, $return->uuid);
        $this->assertEquals($box->goldenId, $return->goldenId);
        $this->assertEquals($box->status, $return->status);
        $this->assertEquals($box->brand, $return->brand);
        $this->assertEquals($box->country, $return->country);
        $this->assertEquals($box->createdAt, $return->createdAt);
        $this->assertEquals($box->updatedAt, $return->updatedAt);
    }

    /**
     * @covers ::put
     */
    public function testIfPutThrowsResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $boxUpdateRequest = new BoxUpdateRequest();
        $boxManager = $this->prophesize(BoxManager::class);
        $boxManager->update($uuid, $boxUpdateRequest)->willThrow(BoxNotFoundException::class);
        $controller = new BoxController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->put(Uuid::fromString($uuid), $boxUpdateRequest, $boxManager->reveal());
    }

    /**
     * @covers ::put
     * @dataProvider sampleBox
     */
    public function testPut(string $uuid, Box $box)
    {
        $boxUpdateRequest = new BoxUpdateRequest();
        $boxManager = $this->prophesize(BoxManager::class);
        $boxManager->update($uuid, $boxUpdateRequest)->shouldBeCalled()->willReturn($box);
        $controller = new BoxController();
        $response = $controller->put(Uuid::fromString($uuid), $boxUpdateRequest, $boxManager->reveal());
        $this->assertInstanceOf(BoxUpdateResponse::class, $response);
        $this->assertEquals(200, $response->getHttpCode());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $boxManager = $this->prophesize(BoxManager::class);
        $boxManager->delete($uuid)->willThrow(BoxNotFoundException::class);
        $controller = new BoxController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->delete(Uuid::fromString($uuid), $boxManager->reveal());
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $boxManager = $this->prophesize(BoxManager::class);
        $boxManager->delete($uuid)->shouldBeCalled();
        $controller = new BoxController();
        $response = $controller->delete(Uuid::fromString($uuid), $boxManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\Internal\Box\BoxCreateResponse::__construct
     *
     * @dataProvider sampleBox
     */
    public function testCreate(string $uuid, Box $box)
    {
        $boxCreateRequest = new BoxCreateRequest();
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $boxManager = $this->prophesize(BoxManager::class);
        $boxManager->create($boxCreateRequest)->willReturn($box);
        $controller = new BoxController();
        $boxCreateResponse = $controller->create($boxCreateRequest, $boxManager->reveal());

        $this->assertEquals($uuid, $boxCreateResponse->uuid);
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\Internal\Box\BoxCreateResponse::__construct
     */
    public function testCreateWithExistingGoldenId()
    {
        $boxCreateRequest = new BoxCreateRequest();
        $boxManager = $this->prophesize(BoxManager::class);
        $exception = new UniqueConstraintViolationException('', $this->prophesize(DriverException::class)->reveal());
        $boxManager->create($boxCreateRequest)->willThrow($exception);
        $controller = new BoxController();
        $this->expectException(ResourceConflictException::class);
        $controller->create($boxCreateRequest, $boxManager->reveal());
    }

    public function sampleBox(): iterable
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $box = new Box();
        $box->uuid = $uuidInterface->reveal();
        $box->goldenId = '1234';
        $box->brand = 'sbx';
        $box->country = 'fr';
        $box->status = 'created';
        $box->createdAt = new \DateTime();
        $box->updatedAt = new \DateTime();

        yield [$uuid, $box];
    }
}
