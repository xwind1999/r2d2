<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Contract\Request\Box\BoxCreateRequest;
use App\Contract\Request\Box\BoxUpdateRequest;
use App\Contract\Response\Box\BoxGetResponse;
use App\Controller\Api\BoxController;
use App\Entity\Box;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\BoxManager;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Controller\Api\BoxController
 */
class BoxControllerTest extends TestCase
{
    /**
     * @covers ::get
     * @covers \App\Contract\Response\Box\BoxGetResponse::__construct
     */
    public function testIfGetWillThrowResourceNotFoundException(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $controller = new BoxController();
        $boxManager = $this->prophesize(BoxManager::class);
        $boxManager->get($uuid)->willThrow(EntityNotFoundException::class);
        $this->expectException(ResourceNotFoundException::class);
        $controller->get($uuid, $boxManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\Box\BoxGetResponse::__construct
     */
    public function testIfGetWillThrowUnprocessableEntityException(): void
    {
        $controller = new BoxController();
        $boxManager = $this->prophesize(BoxManager::class);
        $this->expectException(UnprocessableEntityException::class);
        $controller->get('12345', $boxManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\Box\BoxGetResponse::__construct
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
        $return = $controller->get($uuid, $boxManager->reveal());
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
        $boxManager->update($uuid, $boxUpdateRequest)->willThrow(EntityNotFoundException::class);
        $controller = new BoxController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->put($uuid, $boxUpdateRequest, $boxManager->reveal());
    }

    /**
     * @covers ::put
     */
    public function testPut()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $boxUpdateRequest = new BoxUpdateRequest();
        $boxManager = $this->prophesize(BoxManager::class);
        $boxManager->update($uuid, $boxUpdateRequest)->shouldBeCalled();
        $controller = new BoxController();
        $response = $controller->put($uuid, $boxUpdateRequest, $boxManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowUnprocessableEntityException()
    {
        $boxManager = $this->prophesize(BoxManager::class);
        $controller = new BoxController();
        $this->expectException(UnprocessableEntityException::class);
        $controller->delete('1234', $boxManager->reveal());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $boxManager = $this->prophesize(BoxManager::class);
        $boxManager->delete($uuid)->willThrow(EntityNotFoundException::class);
        $controller = new BoxController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->delete($uuid, $boxManager->reveal());
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
        $response = $controller->delete($uuid, $boxManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\Box\BoxCreateResponse::__construct
     */
    public function testCreate()
    {
        $boxCreateRequest = new BoxCreateRequest();
        $uuid = '1234';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $box = new Box();
        $box->uuid = $uuidInterface->reveal();
        $boxManager = $this->prophesize(BoxManager::class);
        $boxManager->create($boxCreateRequest)->willReturn($box);
        $controller = new BoxController();
        $boxCreateResponse = $controller->create($boxCreateRequest, $boxManager->reveal());

        $this->assertEquals($uuid, $boxCreateResponse->uuid);
    }
}
