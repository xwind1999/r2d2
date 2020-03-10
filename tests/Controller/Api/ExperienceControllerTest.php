<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Contract\Request\Experience\ExperienceCreateRequest;
use App\Contract\Request\Experience\ExperienceUpdateRequest;
use App\Contract\Response\Experience\ExperienceGetResponse;
use App\Controller\Api\ExperienceController;
use App\Entity\Experience;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\ExperienceManager;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Controller\Api\ExperienceController
 */
class ExperienceControllerTest extends TestCase
{
    /**
     * @covers ::get
     * @covers \App\Contract\Response\Experience\ExperienceGetResponse::__construct
     */
    public function testIfGetWillThrowResourceNotFoundException(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $controller = new ExperienceController();
        $experienceManager = $this->prophesize(ExperienceManager::class);
        $experienceManager->get($uuid)->willThrow(EntityNotFoundException::class);
        $this->expectException(ResourceNotFoundException::class);
        $controller->get($uuid, $experienceManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\Experience\ExperienceGetResponse::__construct
     */
    public function testIfGetWillThrowUnprocessableEntityException(): void
    {
        $controller = new ExperienceController();
        $experienceManager = $this->prophesize(ExperienceManager::class);
        $this->expectException(UnprocessableEntityException::class);
        $controller->get('12345', $experienceManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\Experience\ExperienceGetResponse::__construct
     */
    public function testGet(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $experience = new Experience();
        $experience->uuid = $uuidInterface->reveal();
        $experience->goldenId = '1234';
        $experience->partnerGoldenId = '5678';
        $experience->name = 'dinner with massage';
        $experience->description = 'a fancy dinner with feet massage';
        $experience->duration = 0;
        $experience->createdAt = new \DateTime();
        $experience->updatedAt = new \DateTime();

        $controller = new ExperienceController();
        $experienceManager = $this->prophesize(ExperienceManager::class);
        $experienceManager->get($uuid)->willReturn($experience);
        $return = $controller->get($uuid, $experienceManager->reveal());
        $this->assertEquals(ExperienceGetResponse::class, get_class($return));
        $this->assertEquals($uuid, $return->uuid);
        $this->assertEquals($experience->goldenId, $return->goldenId);
        $this->assertEquals($experience->partnerGoldenId, $return->partnerGoldenId);
        $this->assertEquals($experience->name, $return->name);
        $this->assertEquals($experience->description, $return->description);
        $this->assertEquals($experience->duration, $return->duration);
        $this->assertEquals($experience->createdAt, $return->createdAt);
        $this->assertEquals($experience->updatedAt, $return->updatedAt);
    }

    /**
     * @covers ::put
     */
    public function testIfPutThrowsResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $experienceUpdateRequest = new ExperienceUpdateRequest();
        $experienceManager = $this->prophesize(ExperienceManager::class);
        $experienceManager->update($uuid, $experienceUpdateRequest)->willThrow(EntityNotFoundException::class);
        $controller = new ExperienceController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->put($uuid, $experienceUpdateRequest, $experienceManager->reveal());
    }

    /**
     * @covers ::put
     */
    public function testPut()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $experienceUpdateRequest = new ExperienceUpdateRequest();
        $experienceManager = $this->prophesize(ExperienceManager::class);
        $experienceManager->update($uuid, $experienceUpdateRequest)->shouldBeCalled();
        $controller = new ExperienceController();
        $response = $controller->put($uuid, $experienceUpdateRequest, $experienceManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowUnprocessableEntityException()
    {
        $experienceManager = $this->prophesize(ExperienceManager::class);
        $controller = new ExperienceController();
        $this->expectException(UnprocessableEntityException::class);
        $controller->delete('1234', $experienceManager->reveal());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $experienceManager = $this->prophesize(ExperienceManager::class);
        $experienceManager->delete($uuid)->willThrow(EntityNotFoundException::class);
        $controller = new ExperienceController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->delete($uuid, $experienceManager->reveal());
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $experienceManager = $this->prophesize(ExperienceManager::class);
        $experienceManager->delete($uuid)->shouldBeCalled();
        $controller = new ExperienceController();
        $response = $controller->delete($uuid, $experienceManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\Experience\ExperienceCreateResponse::__construct
     */
    public function testCreate()
    {
        $experienceCreateRequest = new ExperienceCreateRequest();
        $uuid = '1234';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $experience = new Experience();
        $experience->uuid = $uuidInterface->reveal();
        $experienceManager = $this->prophesize(ExperienceManager::class);
        $experienceManager->create($experienceCreateRequest)->willReturn($experience);
        $controller = new ExperienceController();
        $experienceCreateResponse = $controller->create($experienceCreateRequest, $experienceManager->reveal());

        $this->assertEquals($uuid, $experienceCreateResponse->uuid);
    }
}
