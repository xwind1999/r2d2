<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\Experience\ExperienceCreateRequest;
use App\Contract\Request\Experience\ExperienceUpdateRequest;
use App\Entity\Experience;
use App\Manager\ExperienceManager;
use App\Repository\ExperienceRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Manager\ExperienceManager
 */
class ExperienceManagerTest extends TestCase
{
    /**
     * @var EntityManagerInterface|ObjectProphecy
     */
    protected $em;

    /**
     * @var ExperienceRepository|ObjectProphecy
     */
    protected $repository;

    public function setUp(): void
    {
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->repository = $this->prophesize(ExperienceRepository::class);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::update
     */
    public function testUpdate()
    {
        $manager = new ExperienceManager($this->em->reveal(), $this->repository->reveal());
        $experienceUpdateRequest = new ExperienceUpdateRequest();
        $uuid = '12345678';
        $experienceUpdateRequest->uuid = $uuid;
        $experienceUpdateRequest->goldenId = '1234';
        $experienceUpdateRequest->partnerGoldenId = '5678';
        $experienceUpdateRequest->name = 'dinner with massage';
        $experienceUpdateRequest->description = 'a fancy dinner with feet massage';
        $experienceUpdateRequest->duration = 1;

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);

        $experience = new Experience();
        $experience->uuid = $uuidInterface->reveal();
        $experience->goldenId = '1234';
        $experience->partnerGoldenId = '5678';
        $experience->name = '7895';
        $experience->description = '12365488';
        $experience->duration = 0;
        $this->repository->findOne($uuid)->willReturn($experience);

        $this->em->persist(Argument::type(Experience::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $manager->update($experienceUpdateRequest);

        $this->assertEquals(1, $experience->duration);
        $this->assertEquals('5678', $experience->partnerGoldenId);
        $this->assertEquals('dinner with massage', $experience->name);
        $this->assertEquals('a fancy dinner with feet massage', $experience->description);
        $this->assertEquals('1234', $experience->goldenId);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::delete
     */
    public function testDelete()
    {
        $manager = new ExperienceManager($this->em->reveal(), $this->repository->reveal());
        $uuid = '12345678';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $experience = new Experience();
        $experience->uuid = $uuidInterface->reveal();
        $this->repository->findOne($uuid)->willReturn($experience);

        $this->em->remove(Argument::type(Experience::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $manager->delete($uuid);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreate()
    {
        $manager = new ExperienceManager($this->em->reveal(), $this->repository->reveal());
        $experienceCreateRequest = new ExperienceCreateRequest();
        $experienceCreateRequest->goldenId = '5678';
        $experienceCreateRequest->partnerGoldenId = '5678';
        $experienceCreateRequest->name = 'dinner with massage';
        $experienceCreateRequest->description = 'a fancy dinner with feet massage';
        $experienceCreateRequest->duration = 0;

        $this->em->persist(Argument::type(Experience::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $experience = $manager->create($experienceCreateRequest);
        $this->assertEquals($experienceCreateRequest->goldenId, $experience->goldenId);
        $this->assertEquals($experienceCreateRequest->partnerGoldenId, $experience->partnerGoldenId);
        $this->assertEquals($experienceCreateRequest->name, $experience->name);
        $this->assertEquals($experienceCreateRequest->description, $experience->description);
        $this->assertEquals($experienceCreateRequest->duration, $experience->duration);
    }
}
