<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\ExperienceComponent\ExperienceComponentCreateRequest;
use App\Contract\Request\ExperienceComponent\ExperienceComponentDeleteRequest;
use App\Contract\Request\ExperienceComponent\ExperienceComponentUpdateRequest;
use App\Entity\Experience;
use App\Entity\ExperienceComponent;
use App\Entity\Room;
use App\Exception\Manager\ExperienceComponent\RelationshipAlreadyExistsException;
use App\Exception\Repository\ExperienceComponentNotFoundException;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Exception\Repository\RoomNotFoundException;
use App\Manager\ExperienceComponentManager;
use App\Repository\ExperienceComponentRepository;
use App\Repository\ExperienceRepository;
use App\Repository\RoomRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \App\Manager\ExperienceComponentManager
 */
class ExperienceComponentManagerTest extends TestCase
{
    /**
     * @var ExperienceComponentRepository|ObjectProphecy
     */
    protected $experienceComponentRepository;

    /**
     * @var ObjectProphecy|RoomRepository
     */
    protected $roomRepository;

    /**
     * @var ExperienceRepository|ObjectProphecy
     */
    protected $experienceRepository;

    public function setUp(): void
    {
        $this->experienceComponentRepository = $this->prophesize(ExperienceComponentRepository::class);
        $this->roomRepository = $this->prophesize(RoomRepository::class);
        $this->experienceRepository = $this->prophesize(ExperienceRepository::class);
    }

    /**
     * @covers ::__construct
     * @covers ::delete
     */
    public function testDelete()
    {
        $manager = new ExperienceComponentManager(
            $this->experienceComponentRepository->reveal(),
            $this->roomRepository->reveal(),
            $this->experienceRepository->reveal()
        );

        $experienceComponent = new ExperienceComponent();
        $room = new Room();
        $room->goldenId = '1234';
        $experience = new Experience();
        $experienceComponentDeleteRequest = new ExperienceComponentDeleteRequest();
        $experienceComponentDeleteRequest->experienceGoldenId = '1234';
        $experienceComponentDeleteRequest->roomGoldenId = '1234';

        $this->roomRepository->findOneByGoldenId($experienceComponentDeleteRequest->roomGoldenId)->willReturn($room);
        $this->experienceRepository
            ->findOneByGoldenId($experienceComponentDeleteRequest->experienceGoldenId)
            ->willReturn($experience)
        ;

        $this->experienceComponentRepository->findOneByExperienceComponent($experience, $room)->willReturn($experienceComponent);

        $this->experienceComponentRepository->delete($experienceComponent)->shouldBeCalled();

        $manager->delete($experienceComponentDeleteRequest);
    }

    /**
     * @covers ::__construct
     * @covers ::delete
     */
    public function testDeleteWithRelationshipThatDoesntExist()
    {
        $manager = new ExperienceComponentManager(
            $this->experienceComponentRepository->reveal(),
            $this->roomRepository->reveal(),
            $this->experienceRepository->reveal()
        );

        $experienceComponent = new ExperienceComponent();
        $room = new Room();
        $room->goldenId = '1234';
        $experience = new Experience();
        $experienceComponentDeleteRequest = new ExperienceComponentDeleteRequest();
        $experienceComponentDeleteRequest->experienceGoldenId = '1234';
        $experienceComponentDeleteRequest->roomGoldenId = '1234';

        $this->roomRepository->findOneByGoldenId($experienceComponentDeleteRequest->roomGoldenId)->willReturn($room);
        $this->experienceRepository
            ->findOneByGoldenId($experienceComponentDeleteRequest->experienceGoldenId)
            ->willReturn($experience)
        ;

        $this->experienceComponentRepository->findOneByExperienceComponent($experience, $room)->willReturn(null);

        $this->experienceComponentRepository->delete($experienceComponent)->shouldNotBeCalled();

        $manager->delete($experienceComponentDeleteRequest);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreate()
    {
        $manager = new ExperienceComponentManager(
            $this->experienceComponentRepository->reveal(),
            $this->roomRepository->reveal(),
            $this->experienceRepository->reveal()
        );

        $room = new Room();
        $room->goldenId = '5678';
        $this->roomRepository->findOneByGoldenId('5678')->willReturn($room);

        $experience = new Experience();
        $experience->goldenId = '9012';
        $this->experienceRepository->findOneByGoldenId('9012')->willReturn($experience);

        $currentDate = new \DateTime();
        $experienceComponentCreateRequest = new ExperienceComponentCreateRequest();

        $experienceComponentCreateRequest->roomGoldenId = '5678';
        $experienceComponentCreateRequest->experienceGoldenId = '9012';
        $experienceComponentCreateRequest->isEnabled = true;
        $experienceComponentCreateRequest->externalUpdatedAt = $currentDate;

        $this->experienceComponentRepository->findOneByExperienceComponent($experience, $room)->willReturn(null);
        $this->experienceComponentRepository->save(Argument::type(ExperienceComponent::class))->shouldBeCalled();

        $booking = $manager->create($experienceComponentCreateRequest);
        $this->assertEquals($experienceComponentCreateRequest->roomGoldenId, $booking->roomGoldenId);
        $this->assertEquals($experienceComponentCreateRequest->experienceGoldenId, $booking->experienceGoldenId);
        $this->assertEquals($experienceComponentCreateRequest->externalUpdatedAt, $booking->externalUpdatedAt);
    }

    /**
     * @covers ::__construct
     * @covers ::update
     */
    public function testUpdate()
    {
        $manager = new ExperienceComponentManager(
            $this->experienceComponentRepository->reveal(),
            $this->roomRepository->reveal(),
            $this->experienceRepository->reveal()
        );

        $room = new Room();
        $room->goldenId = '1234';
        $this->roomRepository->findOneByGoldenId('1234')->willReturn($room);

        $experience = new Experience();
        $experience->goldenId = '7895';
        $this->experienceRepository->findOneByGoldenId('7895')->willReturn($experience);

        $date = new \DateTime();
        $experienceComponentUpdateRequest = new ExperienceComponentUpdateRequest();
        $experienceComponentUpdateRequest->roomGoldenId = '1234';
        $experienceComponentUpdateRequest->experienceGoldenId = '7895';
        $experienceComponentUpdateRequest->isEnabled = false;
        $experienceComponentUpdateRequest->externalUpdatedAt = $date;

        $experienceComponent = new ExperienceComponent();
        $experienceComponent->roomGoldenId = '1234';
        $experienceComponent->experienceGoldenId = '7895';
        $experienceComponent->isEnabled = true;
        $experienceComponent->externalUpdatedAt = $date;
        $this->experienceComponentRepository->findOneByExperienceComponent($experience, $room)->willReturn($experienceComponent);

        $this->experienceComponentRepository->save(Argument::type(ExperienceComponent::class))->shouldBeCalled();

        $updatedExperienceComponent = $manager->update($experienceComponentUpdateRequest);

        $this->assertSame($experienceComponent, $updatedExperienceComponent);
        $this->assertEquals('1234', $experienceComponent->roomGoldenId);
        $this->assertEquals('7895', $experienceComponent->experienceGoldenId);
        $this->assertEquals(false, $experienceComponent->isEnabled);
        $this->assertEquals($date, $experienceComponent->externalUpdatedAt);
    }

    /**
     * @covers ::__construct
     * @covers ::update
     */
    public function testUpdateWithoutExperienceComponent()
    {
        $manager = new ExperienceComponentManager(
            $this->experienceComponentRepository->reveal(),
            $this->roomRepository->reveal(),
            $this->experienceRepository->reveal()
        );
        $room = new Room();
        $room->goldenId = '1234';
        $this->roomRepository->findOneByGoldenId('1234')->willReturn($room);

        $experience = new Experience();
        $experience->goldenId = '7895';
        $this->experienceRepository->findOneByGoldenId('7895')->willReturn($experience);

        $experienceComponentUpdateRequest = new ExperienceComponentUpdateRequest();
        $experienceComponentUpdateRequest->roomGoldenId = '1234';
        $experienceComponentUpdateRequest->experienceGoldenId = '7895';
        $experienceComponentUpdateRequest->isEnabled = true;
        $experienceComponentUpdateRequest->externalUpdatedAt = new \DateTime();

        $this->experienceComponentRepository->findOneByExperienceComponent($experience, $room)->willReturn(null);
        $this->experienceComponentRepository->save(Argument::type(ExperienceComponent::class))->shouldNotBeCalled();
        $this->expectException(ExperienceComponentNotFoundException::class);
        $manager->update($experienceComponentUpdateRequest);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreateDuplicate()
    {
        $manager = new ExperienceComponentManager(
            $this->experienceComponentRepository->reveal(),
            $this->roomRepository->reveal(),
            $this->experienceRepository->reveal()
        );

        $room = new Room();
        $room->goldenId = '5678';
        $this->roomRepository->findOneByGoldenId('5678')->willReturn($room);

        $experience = new Experience();
        $experience->goldenId = '9012';
        $this->experienceRepository->findOneByGoldenId('9012')->willReturn($experience);

        $currentDate = new \DateTime();
        $experienceComponentCreateRequest = new ExperienceComponentCreateRequest();

        $experienceComponentCreateRequest->roomGoldenId = '5678';
        $experienceComponentCreateRequest->experienceGoldenId = '9012';
        $experienceComponentCreateRequest->externalUpdatedAt = $currentDate;

        $this->expectException(RelationshipAlreadyExistsException::class);
        $this->experienceComponentRepository->findOneByExperienceComponent($experience, $room)->willReturn(new ExperienceComponent());
        $booking = $manager->create($experienceComponentCreateRequest);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreateWithInvalidRoomGoldenId()
    {
        $manager = new ExperienceComponentManager(
            $this->experienceComponentRepository->reveal(),
            $this->roomRepository->reveal(),
            $this->experienceRepository->reveal()
        );
        $this->roomRepository->findOneByGoldenId('5678')->willThrow(RoomNotFoundException::class);

        $experienceComponentCreateRequest = new ExperienceComponentCreateRequest();
        $experienceComponentCreateRequest->roomGoldenId = '5678';

        $this->expectException(RoomNotFoundException::class);
        $manager->create($experienceComponentCreateRequest);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreateWithInvalidExperienceGoldenId()
    {
        $manager = new ExperienceComponentManager(
            $this->experienceComponentRepository->reveal(),
            $this->roomRepository->reveal(),
            $this->experienceRepository->reveal()
        );

        $room = new Room();
        $room->goldenId = '5678';
        $this->roomRepository->findOneByGoldenId('5678')->willReturn($room);

        $this->experienceRepository
            ->findOneByGoldenId('9012')
            ->willThrow(ExperienceNotFoundException::class)
        ;

        $experienceComponentCreateRequest = new ExperienceComponentCreateRequest();

        $experienceComponentCreateRequest->roomGoldenId = '5678';
        $experienceComponentCreateRequest->experienceGoldenId = '9012';

        $this->expectException(ExperienceNotFoundException::class);
        $manager->create($experienceComponentCreateRequest);
    }
}
