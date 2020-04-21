<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Contract\Request\BoxExperience\BoxExperienceCreateRequest;
use App\Contract\Request\BoxExperience\BoxExperienceDeleteRequest;
use App\Controller\Api\BoxExperienceController;
use App\Entity\BoxExperience;
use App\Exception\Http\ResourceConflictException;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Manager\BoxExperience\RelationshipAlreadyExistsException;
use App\Exception\Repository\BoxNotFoundException;
use App\Manager\BoxExperienceManager;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Controller\Api\BoxExperienceController
 */
class BoxExperienceControllerTest extends TestCase
{
    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowResourceNotFoundException()
    {
        $boxExperienceDeleteRequest = new BoxExperienceDeleteRequest();
        $boxExperienceDeleteRequest->experienceGoldenId = '1234';
        $boxExperienceDeleteRequest->boxGoldenId = '1234';
        $boxExperienceManager = $this->prophesize(BoxExperienceManager::class);
        $boxExperienceManager->delete($boxExperienceDeleteRequest)->willThrow(BoxNotFoundException::class);
        $controller = new BoxExperienceController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->delete($boxExperienceDeleteRequest, $boxExperienceManager->reveal());
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $boxExperienceDeleteRequest = new BoxExperienceDeleteRequest();
        $boxExperienceDeleteRequest->experienceGoldenId = '1234';
        $boxExperienceDeleteRequest->boxGoldenId = '1234';
        $boxExperienceManager = $this->prophesize(BoxExperienceManager::class);
        $boxExperienceManager->delete($boxExperienceDeleteRequest)->shouldBeCalled();
        $controller = new BoxExperienceController();
        $response = $controller->delete($boxExperienceDeleteRequest, $boxExperienceManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\BoxExperience\BoxExperienceCreateResponse::__construct
     * @dataProvider sampleBoxExperience
     */
    public function testCreate(BoxExperience $boxExperience)
    {
        $boxExperienceCreateRequest = new BoxExperienceCreateRequest();
        $boxExperienceManager = $this->prophesize(BoxExperienceManager::class);
        $boxExperienceManager->create($boxExperienceCreateRequest)->willReturn($boxExperience);
        $controller = new BoxExperienceController();
        $boxExperienceCreateResponse = $controller->create($boxExperienceCreateRequest, $boxExperienceManager->reveal());

        $this->assertEquals($boxExperience->experienceGoldenId, $boxExperienceCreateResponse->experienceGoldenId);
        $this->assertEquals($boxExperience->boxGoldenId, $boxExperienceCreateResponse->boxGoldenId);
        $this->assertEquals($boxExperience->externalUpdatedAt, $boxExperienceCreateResponse->externalUpdatedAt);
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\BoxExperience\BoxExperienceCreateResponse::__construct
     * @dataProvider sampleBoxExperience
     */
    public function testCreateWithAnExistingRelationship(BoxExperience $boxExperience)
    {
        $boxExperienceCreateRequest = new BoxExperienceCreateRequest();
        $boxExperienceManager = $this->prophesize(BoxExperienceManager::class);
        $boxExperienceManager->create($boxExperienceCreateRequest)->willThrow(RelationshipAlreadyExistsException::class);

        $this->expectException(ResourceConflictException::class);
        $controller = new BoxExperienceController();
        $controller->create($boxExperienceCreateRequest, $boxExperienceManager->reveal());
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\BoxExperience\BoxExperienceCreateResponse::__construct
     * @dataProvider sampleBoxExperience
     */
    public function testCreateWithInvalidBoxOrExperience(BoxExperience $boxExperience)
    {
        $boxExperienceCreateRequest = new BoxExperienceCreateRequest();
        $boxExperienceManager = $this->prophesize(BoxExperienceManager::class);
        $boxExperienceManager->create($boxExperienceCreateRequest)->willThrow(BoxNotFoundException::class);

        $this->expectException(ResourceNotFoundException::class);
        $controller = new BoxExperienceController();
        $controller->create($boxExperienceCreateRequest, $boxExperienceManager->reveal());
    }

    public function sampleBoxExperience(): iterable
    {
        $boxExperience = new BoxExperience();
        $boxExperience->experienceGoldenId = '9012';
        $boxExperience->boxGoldenId = '1234';
        $boxExperience->isEnabled = true;
        $boxExperience->externalUpdatedAt = new \DateTime('2020-05-05');

        yield [$boxExperience];
    }
}
