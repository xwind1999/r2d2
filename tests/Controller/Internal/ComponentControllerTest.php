<?php

declare(strict_types=1);

namespace App\Tests\Controller\Internal;

use App\Contract\Request\Internal\Component\ComponentCreateRequest;
use App\Contract\Request\Internal\Component\ComponentUpdateRequest;
use App\Contract\Response\Internal\Component\ComponentGetResponse;
use App\Contract\Response\Internal\Component\ComponentUpdateResponse;
use App\Controller\Internal\ComponentController;
use App\Entity\Component;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Repository\ComponentNotFoundException;
use App\Manager\ComponentManager;
use App\Tests\ProphecyTestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Controller\Internal\ComponentController
 */
class ComponentControllerTest extends ProphecyTestCase
{
    /**
     * @covers ::get
     * @covers \App\Contract\Response\Internal\Component\ComponentGetResponse::__construct
     */
    public function testIfGetWillThrowResourceNotFoundException(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $controller = new ComponentController();
        $componentManager = $this->prophesize(ComponentManager::class);
        $componentManager->get($uuid)->willThrow(ComponentNotFoundException::class);
        $this->expectException(ResourceNotFoundException::class);
        $controller->get(Uuid::fromString($uuid), $componentManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\Internal\Component\ComponentGetResponse::__construct
     */
    public function testGet(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $component = new Component();
        $component->uuid = $uuidInterface->reveal();
        $component->goldenId = '5678';
        $component->partnerGoldenId = '5678';
        $component->name = 'room with small bed';
        $component->description = 'the bed is very small';
        $component->inventory = 1;
        $component->duration = 0;
        $component->durationUnit = 'day';
        $component->isSellable = false;
        $component->isReservable = false;
        $component->status = 'ok';
        $component->createdAt = new \DateTime();
        $component->updatedAt = new \DateTime();

        $controller = new ComponentController();
        $componentManager = $this->prophesize(ComponentManager::class);
        $componentManager->get($uuid)->willReturn($component);
        $return = $controller->get(Uuid::fromString($uuid), $componentManager->reveal());
        $this->assertEquals(ComponentGetResponse::class, get_class($return));
        $this->assertEquals($uuid, $return->uuid);
        $this->assertEquals($component->goldenId, $return->goldenId);
        $this->assertEquals($component->partnerGoldenId, $return->partnerGoldenId);
        $this->assertEquals($component->name, $return->name);
        $this->assertEquals($component->description, $return->description);
        $this->assertEquals($component->inventory, $return->inventory);
        $this->assertEquals($component->durationUnit, $return->durationUnit);
        $this->assertEquals($component->isSellable, $return->isSellable);
        $this->assertEquals($component->isReservable, $return->isReservable);
        $this->assertEquals($component->status, $return->status);
        $this->assertEquals($component->createdAt, $return->createdAt);
        $this->assertEquals($component->updatedAt, $return->updatedAt);
    }

    /**
     * @covers ::put
     */
    public function testIfPutThrowsResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $componentUpdateRequest = new ComponentUpdateRequest();
        $componentManager = $this->prophesize(ComponentManager::class);
        $componentManager->update($uuid, $componentUpdateRequest)->willThrow(ComponentNotFoundException::class);
        $controller = new ComponentController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->put(Uuid::fromString($uuid), $componentUpdateRequest, $componentManager->reveal());
    }

    /**
     * @covers ::put
     * @dataProvider sampleRoom
     */
    public function testPut(string $uuid, Component $component)
    {
        $componentUpdateRequest = new ComponentUpdateRequest();
        $componentManager = $this->prophesize(ComponentManager::class);
        $componentManager->update($uuid, $componentUpdateRequest)->shouldBeCalled()->willReturn($component);
        $controller = new ComponentController();
        $response = $controller->put(Uuid::fromString($uuid), $componentUpdateRequest, $componentManager->reveal());
        $this->assertInstanceOf(ComponentUpdateResponse::class, $response);
        $this->assertEquals(200, $response->getHttpCode());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $componentManager = $this->prophesize(ComponentManager::class);
        $componentManager->delete($uuid)->willThrow(ComponentNotFoundException::class);
        $controller = new ComponentController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->delete(Uuid::fromString($uuid), $componentManager->reveal());
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $componentManager = $this->prophesize(ComponentManager::class);
        $componentManager->delete($uuid)->shouldBeCalled();
        $controller = new ComponentController();
        $response = $controller->delete(Uuid::fromString($uuid), $componentManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\Internal\Component\ComponentCreateResponse::__construct
     * @dataProvider sampleRoom
     */
    public function testCreate(string $uuid, Component $component)
    {
        $roomCreateRequest = new ComponentCreateRequest();
        $componentManager = $this->prophesize(ComponentManager::class);
        $componentManager->create($roomCreateRequest)->willReturn($component);
        $controller = new ComponentController();
        $roomCreateResponse = $controller->create($roomCreateRequest, $componentManager->reveal());

        $this->assertEquals($uuid, $roomCreateResponse->uuid);
    }

    public function sampleRoom(): iterable
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $component = new Component();
        $component->uuid = $uuidInterface->reveal();
        $component->goldenId = '1234';
        $component->partnerGoldenId = '1234';
        $component->name = 'test room';
        $component->description = 'this is a test room';
        $component->inventory = 2;
        $component->duration = 2;
        $component->durationUnit = 'day';
        $component->isSellable = true;
        $component->isReservable = true;
        $component->status = 'enabled';
        $component->createdAt = new \DateTime();
        $component->updatedAt = new \DateTime();

        yield [$uuid, $component];
    }
}
