<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Contract\Request\Experience\ExperienceCreateRequest;
use App\Contract\Request\Experience\ExperienceUpdateRequest;
use App\Contract\Response\Experience\ExperienceGetResponse;
use App\Contract\Response\Experience\ExperienceUpdateResponse;
use App\Controller\Api\ExperienceController;
use App\Entity\Experience;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Manager\ExperienceManager;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
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
        $experienceManager->get($uuid)->willThrow(ExperienceNotFoundException::class);
        $this->expectException(ResourceNotFoundException::class);
        $controller->get(Uuid::fromString($uuid), $experienceManager->reveal());
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
        $return = $controller->get(Uuid::fromString($uuid), $experienceManager->reveal());
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
        $experienceManager->update($uuid, $experienceUpdateRequest)->willThrow(ExperienceNotFoundException::class);
        $controller = new ExperienceController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->put(Uuid::fromString($uuid), $experienceUpdateRequest, $experienceManager->reveal());
    }

    /**
     * @covers ::put
     * @dataProvider sampleExperience
     */
    public function testPut(string $uuid, Experience $experience)
    {
        $experienceUpdateRequest = new ExperienceUpdateRequest();
        $experienceManager = $this->prophesize(ExperienceManager::class);
        $experienceManager->update($uuid, $experienceUpdateRequest)->shouldBeCalled()->willReturn($experience);
        $controller = new ExperienceController();
        $response = $controller->put(Uuid::fromString($uuid), $experienceUpdateRequest, $experienceManager->reveal());
        $this->assertEquals(200, $response->getHttpCode());
        $this->assertInstanceOf(ExperienceUpdateResponse::class, $response);
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $experienceManager = $this->prophesize(ExperienceManager::class);
        $experienceManager->delete($uuid)->willThrow(ExperienceNotFoundException::class);
        $controller = new ExperienceController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->delete(Uuid::fromString($uuid), $experienceManager->reveal());
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
        $response = $controller->delete(Uuid::fromString($uuid), $experienceManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\Experience\ExperienceCreateResponse::__construct
     *
     * @dataProvider sampleExperience
     */
    public function testCreate(string $uuid, Experience $experience)
    {
        $experienceCreateRequest = new ExperienceCreateRequest();
        $experienceManager = $this->prophesize(ExperienceManager::class);
        $experienceManager->create($experienceCreateRequest)->willReturn($experience);
        $controller = new ExperienceController();
        $experienceCreateResponse = $controller->create($experienceCreateRequest, $experienceManager->reveal());

        $this->assertEquals($uuid, $experienceCreateResponse->uuid);
    }

    public function sampleExperience(): iterable
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $experience = new Experience();
        $experience->uuid = $uuidInterface->reveal();
        $experience->goldenId = '1234';
        $experience->partnerGoldenId = '1234';
        $experience->name = '1234';
        $experience->description = '1234';
        $experience->duration = 2;
        $experience->createdAt = new \DateTime();
        $experience->updatedAt = new \DateTime();

        yield [$uuid, $experience];
    }
}
