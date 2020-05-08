<?php

declare(strict_types=1);

namespace App\Tests\Controller\Internal;

use App\Contract\Request\Internal\ExperienceComponent\ExperienceComponentCreateRequest;
use App\Contract\Request\Internal\ExperienceComponent\ExperienceComponentDeleteRequest;
use App\Contract\Request\Internal\ExperienceComponent\ExperienceComponentUpdateRequest;
use App\Contract\Response\Internal\ExperienceComponent\ExperienceComponentUpdateResponse;
use App\Controller\Api\ExperienceComponentController;
use App\Entity\ExperienceComponent;
use App\Exception\Http\ResourceConflictException;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Manager\ExperienceComponent\RelationshipAlreadyExistsException;
use App\Exception\Repository\BoxNotFoundException;
use App\Exception\Repository\ExperienceComponentNotFoundException;
use App\Manager\ExperienceComponentManager;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Controller\Api\ExperienceComponentController
 */
class ExperienceComponentControllerTest extends TestCase
{
    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowResourceNotFoundException()
    {
        $experienceComponentDeleteRequest = new ExperienceComponentDeleteRequest();
        $experienceComponentDeleteRequest->experienceGoldenId = '1234';
        $experienceComponentDeleteRequest->componentGoldenId = '1234';
        $experienceComponentManager = $this->prophesize(ExperienceComponentManager::class);
        $experienceComponentManager->delete($experienceComponentDeleteRequest)->willThrow(BoxNotFoundException::class);
        $controller = new ExperienceComponentController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->delete($experienceComponentDeleteRequest, $experienceComponentManager->reveal());
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $experienceComponentDeleteRequest = new ExperienceComponentDeleteRequest();
        $experienceComponentDeleteRequest->experienceGoldenId = '1234';
        $experienceComponentDeleteRequest->componentGoldenId = '1234';
        $experienceComponentManager = $this->prophesize(ExperienceComponentManager::class);
        $experienceComponentManager->delete($experienceComponentDeleteRequest)->shouldBeCalled();
        $controller = new ExperienceComponentController();
        $response = $controller->delete($experienceComponentDeleteRequest, $experienceComponentManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::create
     * @covers       \App\Contract\Response\Internal\ExperienceComponent\ExperienceComponentCreateResponse::__construct
     * @dataProvider sampleExperienceComponent
     */
    public function testCreate(ExperienceComponent $experienceComponent)
    {
        $experienceComponentCreateRequest = new ExperienceComponentCreateRequest();
        $experienceComponentManager = $this->prophesize(ExperienceComponentManager::class);
        $experienceComponentManager->create($experienceComponentCreateRequest)->willReturn($experienceComponent);
        $controller = new ExperienceComponentController();
        $experienceComponentCreateResponse = $controller->create($experienceComponentCreateRequest, $experienceComponentManager->reveal());

        $this->assertEquals($experienceComponent->experienceGoldenId, $experienceComponentCreateResponse->experienceGoldenId);
        $this->assertEquals($experienceComponent->componentGoldenId, $experienceComponentCreateResponse->componentGoldenId);
        $this->assertEquals($experienceComponent->externalUpdatedAt, $experienceComponentCreateResponse->externalUpdatedAt);
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\Internal\ExperienceComponent\ExperienceComponentCreateResponse::__construct
     * @dataProvider sampleExperienceComponent
     */
    public function testCreateWithAnExistingRelationship(ExperienceComponent $experienceComponent)
    {
        $experienceComponentCreateRequest = new ExperienceComponentCreateRequest();
        $experienceComponentManager = $this->prophesize(ExperienceComponentManager::class);
        $experienceComponentManager->create($experienceComponentCreateRequest)->willThrow(RelationshipAlreadyExistsException::class);

        $this->expectException(ResourceConflictException::class);
        $controller = new ExperienceComponentController();
        $controller->create($experienceComponentCreateRequest, $experienceComponentManager->reveal());
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\Internal\ExperienceComponent\ExperienceComponentCreateResponse::__construct
     * @dataProvider sampleExperienceComponent
     */
    public function testCreateWithInvalidBoxOrExperience(ExperienceComponent $experienceComponent)
    {
        $experienceComponentCreateRequest = new ExperienceComponentCreateRequest();
        $experienceComponentManager = $this->prophesize(ExperienceComponentManager::class);
        $experienceComponentManager->create($experienceComponentCreateRequest)->willThrow(BoxNotFoundException::class);

        $this->expectException(ResourceNotFoundException::class);
        $controller = new ExperienceComponentController();
        $controller->create($experienceComponentCreateRequest, $experienceComponentManager->reveal());
    }

    /**
     * @covers ::put
     */
    public function testIfPutThrowsResourceNotFoundException()
    {
        $experienceComponentUpdateRequest = new ExperienceComponentUpdateRequest();
        $experienceComponentManager = $this->prophesize(ExperienceComponentManager::class);
        $experienceComponentManager
            ->update($experienceComponentUpdateRequest)
            ->willThrow(ExperienceComponentNotFoundException::class)
        ;
        $controller = new ExperienceComponentController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->put($experienceComponentUpdateRequest, $experienceComponentManager->reveal());
    }

    /**
     * @covers ::put
     * @dataProvider sampleExperienceComponent
     */
    public function testPut(ExperienceComponent $experienceComponent)
    {
        $experienceComponentUpdateRequest = new ExperienceComponentUpdateRequest();
        $experienceComponentManager = $this->prophesize(ExperienceComponentManager::class);
        $experienceComponentManager
            ->update($experienceComponentUpdateRequest)
            ->shouldBeCalled()
            ->willReturn($experienceComponent)
        ;
        $controller = new ExperienceComponentController();
        $response = $controller->put($experienceComponentUpdateRequest, $experienceComponentManager->reveal());
        $this->assertInstanceOf(ExperienceComponentUpdateResponse::class, $response);
        $this->assertEquals(200, $response->getHttpCode());
    }

    public function sampleExperienceComponent(): iterable
    {
        $experienceComponent = new ExperienceComponent();
        $experienceComponent->experienceGoldenId = '9012';
        $experienceComponent->componentGoldenId = '1234';
        $experienceComponent->isEnabled = true;
        $experienceComponent->externalUpdatedAt = new \DateTime('2020-05-05');

        yield [$experienceComponent];
    }
}
