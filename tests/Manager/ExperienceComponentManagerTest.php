<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Contract\Request\Internal\ExperienceComponent\ExperienceComponentCreateRequest;
use App\Contract\Request\Internal\ExperienceComponent\ExperienceComponentDeleteRequest;
use App\Contract\Request\Internal\ExperienceComponent\ExperienceComponentUpdateRequest;
use App\Entity\Component;
use App\Entity\Experience;
use App\Entity\ExperienceComponent;
use App\Exception\Manager\ExperienceComponent\RelationshipAlreadyExistsException;
use App\Exception\Repository\ComponentNotFoundException;
use App\Exception\Repository\ExperienceComponentNotFoundException;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Manager\ExperienceComponentManager;
use App\Repository\ComponentRepository;
use App\Repository\ExperienceComponentRepository;
use App\Repository\ExperienceRepository;
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
     * @var ComponentRepository|ObjectProphecy
     */
    protected $componentRepository;

    /**
     * @var ExperienceRepository|ObjectProphecy
     */
    protected $experienceRepository;

    public function setUp(): void
    {
        $this->experienceComponentRepository = $this->prophesize(ExperienceComponentRepository::class);
        $this->componentRepository = $this->prophesize(ComponentRepository::class);
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
            $this->componentRepository->reveal(),
            $this->experienceRepository->reveal()
        );

        $experienceComponent = new ExperienceComponent();
        $component = new Component();
        $component->goldenId = '1234';
        $experience = new Experience();
        $experienceComponentDeleteRequest = new ExperienceComponentDeleteRequest();
        $experienceComponentDeleteRequest->experienceGoldenId = '1234';
        $experienceComponentDeleteRequest->componentGoldenId = '1234';

        $this->componentRepository->findOneByGoldenId($experienceComponentDeleteRequest->componentGoldenId)->willReturn($component);
        $this->experienceRepository
            ->findOneByGoldenId($experienceComponentDeleteRequest->experienceGoldenId)
            ->willReturn($experience)
        ;

        $this->experienceComponentRepository->findOneByExperienceComponent($experience, $component)->willReturn($experienceComponent);

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
            $this->componentRepository->reveal(),
            $this->experienceRepository->reveal()
        );

        $experienceComponent = new ExperienceComponent();
        $component = new Component();
        $component->goldenId = '1234';
        $experience = new Experience();
        $experienceComponentDeleteRequest = new ExperienceComponentDeleteRequest();
        $experienceComponentDeleteRequest->experienceGoldenId = '1234';
        $experienceComponentDeleteRequest->componentGoldenId = '1234';

        $this->componentRepository->findOneByGoldenId($experienceComponentDeleteRequest->componentGoldenId)->willReturn($component);
        $this->experienceRepository
            ->findOneByGoldenId($experienceComponentDeleteRequest->experienceGoldenId)
            ->willReturn($experience)
        ;

        $this->experienceComponentRepository->findOneByExperienceComponent($experience, $component)->willReturn(null);

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
            $this->componentRepository->reveal(),
            $this->experienceRepository->reveal()
        );

        $component = new Component();
        $component->goldenId = '5678';
        $this->componentRepository->findOneByGoldenId('5678')->willReturn($component);

        $experience = new Experience();
        $experience->goldenId = '9012';
        $this->experienceRepository->findOneByGoldenId('9012')->willReturn($experience);

        $currentDate = new \DateTime();
        $experienceComponentCreateRequest = new ExperienceComponentCreateRequest();

        $experienceComponentCreateRequest->componentGoldenId = '5678';
        $experienceComponentCreateRequest->experienceGoldenId = '9012';
        $experienceComponentCreateRequest->isEnabled = true;
        $experienceComponentCreateRequest->externalUpdatedAt = $currentDate;

        $this->experienceComponentRepository->findOneByExperienceComponent($experience, $component)->willReturn(null);
        $this->experienceComponentRepository->save(Argument::type(ExperienceComponent::class))->shouldBeCalled();

        $booking = $manager->create($experienceComponentCreateRequest);
        $this->assertEquals($experienceComponentCreateRequest->componentGoldenId, $booking->componentGoldenId);
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
            $this->componentRepository->reveal(),
            $this->experienceRepository->reveal()
        );

        $component = new Component();
        $component->goldenId = '1234';
        $this->componentRepository->findOneByGoldenId('1234')->willReturn($component);

        $experience = new Experience();
        $experience->goldenId = '7895';
        $this->experienceRepository->findOneByGoldenId('7895')->willReturn($experience);

        $date = new \DateTime();
        $experienceComponentUpdateRequest = new ExperienceComponentUpdateRequest();
        $experienceComponentUpdateRequest->componentGoldenId = '1234';
        $experienceComponentUpdateRequest->experienceGoldenId = '7895';
        $experienceComponentUpdateRequest->isEnabled = false;
        $experienceComponentUpdateRequest->externalUpdatedAt = $date;

        $experienceComponent = new ExperienceComponent();
        $experienceComponent->componentGoldenId = '1234';
        $experienceComponent->experienceGoldenId = '7895';
        $experienceComponent->isEnabled = true;
        $experienceComponent->externalUpdatedAt = $date;
        $this->experienceComponentRepository->findOneByExperienceComponent($experience, $component)->willReturn($experienceComponent);

        $this->experienceComponentRepository->save(Argument::type(ExperienceComponent::class))->shouldBeCalled();

        $updatedExperienceComponent = $manager->update($experienceComponentUpdateRequest);

        $this->assertSame($experienceComponent, $updatedExperienceComponent);
        $this->assertEquals('1234', $experienceComponent->componentGoldenId);
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
            $this->componentRepository->reveal(),
            $this->experienceRepository->reveal()
        );
        $component = new Component();
        $component->goldenId = '1234';
        $this->componentRepository->findOneByGoldenId('1234')->willReturn($component);

        $experience = new Experience();
        $experience->goldenId = '7895';
        $this->experienceRepository->findOneByGoldenId('7895')->willReturn($experience);

        $experienceComponentUpdateRequest = new ExperienceComponentUpdateRequest();
        $experienceComponentUpdateRequest->componentGoldenId = '1234';
        $experienceComponentUpdateRequest->experienceGoldenId = '7895';
        $experienceComponentUpdateRequest->isEnabled = true;
        $experienceComponentUpdateRequest->externalUpdatedAt = new \DateTime();

        $this->experienceComponentRepository->findOneByExperienceComponent($experience, $component)->willReturn(null);
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
            $this->componentRepository->reveal(),
            $this->experienceRepository->reveal()
        );

        $component = new Component();
        $component->goldenId = '5678';
        $this->componentRepository->findOneByGoldenId('5678')->willReturn($component);

        $experience = new Experience();
        $experience->goldenId = '9012';
        $this->experienceRepository->findOneByGoldenId('9012')->willReturn($experience);

        $currentDate = new \DateTime();
        $experienceComponentCreateRequest = new ExperienceComponentCreateRequest();

        $experienceComponentCreateRequest->componentGoldenId = '5678';
        $experienceComponentCreateRequest->experienceGoldenId = '9012';
        $experienceComponentCreateRequest->externalUpdatedAt = $currentDate;

        $this->expectException(RelationshipAlreadyExistsException::class);
        $this->experienceComponentRepository->findOneByExperienceComponent($experience, $component)->willReturn(new ExperienceComponent());
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
            $this->componentRepository->reveal(),
            $this->experienceRepository->reveal()
        );
        $this->componentRepository->findOneByGoldenId('5678')->willThrow(ComponentNotFoundException::class);

        $experienceComponentCreateRequest = new ExperienceComponentCreateRequest();
        $experienceComponentCreateRequest->componentGoldenId = '5678';

        $this->expectException(ComponentNotFoundException::class);
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
            $this->componentRepository->reveal(),
            $this->experienceRepository->reveal()
        );

        $component = new Component();
        $component->goldenId = '5678';
        $this->componentRepository->findOneByGoldenId('5678')->willReturn($component);

        $this->experienceRepository
            ->findOneByGoldenId('9012')
            ->willThrow(ExperienceNotFoundException::class)
        ;

        $experienceComponentCreateRequest = new ExperienceComponentCreateRequest();

        $experienceComponentCreateRequest->componentGoldenId = '5678';
        $experienceComponentCreateRequest->experienceGoldenId = '9012';

        $this->expectException(ExperienceNotFoundException::class);
        $manager->create($experienceComponentCreateRequest);
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplace()
    {
        $manager = new ExperienceComponentManager(
            $this->experienceComponentRepository->reveal(),
            $this->componentRepository->reveal(),
            $this->experienceRepository->reveal()
        );

        $component = new Component();
        $component->goldenId = '1234';
        $this->componentRepository->findOneByGoldenId('1234')->willReturn($component);

        $experience = new Experience();
        $experience->goldenId = '7895';
        $this->experienceRepository->findOneByGoldenId('7895')->willReturn($experience);

        $date = new \DateTime();
        $relationshipRequest = new ProductRelationshipRequest();
        $relationshipRequest->childProduct = '1234';
        $relationshipRequest->parentProduct = '7895';
        $relationshipRequest->isEnabled = false;

        $experienceComponent = new ExperienceComponent();
        $experienceComponent->componentGoldenId = '1234';
        $experienceComponent->experienceGoldenId = '7895';
        $experienceComponent->isEnabled = true;
        $experienceComponent->externalUpdatedAt = $date;
        $this->experienceComponentRepository->findOneByExperienceComponent($experience, $component)->willReturn($experienceComponent);

        $this->experienceComponentRepository->save(Argument::type(ExperienceComponent::class))->shouldBeCalled();

        $updatedExperienceComponent = $manager->replace($relationshipRequest);

        $this->assertSame(null, $updatedExperienceComponent);
    }
}
