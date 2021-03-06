<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Contract\Request\Internal\BoxExperience\BoxExperienceCreateRequest;
use App\Contract\Request\Internal\BoxExperience\BoxExperienceDeleteRequest;
use App\Entity\Box;
use App\Entity\BoxExperience;
use App\Entity\Experience;
use App\Exception\Manager\BoxExperience\OutdatedBoxExperienceRelationshipException;
use App\Exception\Manager\BoxExperience\RelationshipAlreadyExistsException;
use App\Exception\Repository\BoxNotFoundException;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Helper\Manageable\ManageableProductService;
use App\Manager\BoxExperienceManager;
use App\Manager\BoxManager;
use App\Repository\BoxExperienceRepository;
use App\Repository\BoxRepository;
use App\Repository\ExperienceRepository;
use App\Tests\ProphecyTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \App\Manager\BoxExperienceManager
 */
class BoxExperienceManagerTest extends ProphecyTestCase
{
    /**
     * @var BoxExperienceRepository|ObjectProphecy
     */
    protected $repository;

    /**
     * @var BoxRepository|ObjectProphecy
     */
    protected $boxRepository;

    /**
     * @var ExperienceRepository|ObjectProphecy
     */
    protected $experienceRepository;

    /**
     * @var ManageableProductService|ObjectProphecy
     */
    private $manageableProductService;

    /**
     * @var BoxManager|ObjectProphecy
     */
    private $boxManager;

    public function setUp(): void
    {
        $this->repository = $this->prophesize(BoxExperienceRepository::class);
        $this->boxRepository = $this->prophesize(BoxRepository::class);
        $this->experienceRepository = $this->prophesize(ExperienceRepository::class);
        $this->manageableProductService = $this->prophesize(ManageableProductService::class);
        $this->boxManager = $this->prophesize(BoxManager::class);
    }

    /**
     * @covers ::__construct
     * @covers ::delete
     */
    public function testDelete()
    {
        $manager = new BoxExperienceManager(
            $this->repository->reveal(),
            $this->boxRepository->reveal(),
            $this->experienceRepository->reveal(),
            $this->manageableProductService->reveal(),
            $this->boxManager->reveal(),
        );

        $boxExperience = new BoxExperience();
        $box = new Box();
        $box->goldenId = '1234';
        $experience = new Experience();
        $boxExperienceDeleteRequest = new BoxExperienceDeleteRequest();
        $boxExperienceDeleteRequest->experienceGoldenId = '1234';
        $boxExperienceDeleteRequest->boxGoldenId = '1234';

        $this->boxRepository->findOneByGoldenId($boxExperienceDeleteRequest->boxGoldenId)->willReturn($box);
        $this->experienceRepository->findOneByGoldenId($boxExperienceDeleteRequest->experienceGoldenId)->willReturn($experience);

        $this->repository->findOneByBoxExperience($box, $experience)->willReturn($boxExperience);

        $this->repository->delete($boxExperience)->shouldBeCalled();

        $manager->delete($boxExperienceDeleteRequest);
    }

    /**
     * @covers ::__construct
     * @covers ::delete
     */
    public function testDeleteWithRelationshipThatDoesntExist()
    {
        $manager = new BoxExperienceManager(
            $this->repository->reveal(),
            $this->boxRepository->reveal(),
            $this->experienceRepository->reveal(),
            $this->manageableProductService->reveal(),
            $this->boxManager->reveal(),
        );

        $boxExperience = new BoxExperience();
        $box = new Box();
        $box->goldenId = '1234';
        $experience = new Experience();
        $boxExperienceDeleteRequest = new BoxExperienceDeleteRequest();
        $boxExperienceDeleteRequest->experienceGoldenId = '1234';
        $boxExperienceDeleteRequest->boxGoldenId = '1234';

        $this->boxRepository->findOneByGoldenId($boxExperienceDeleteRequest->boxGoldenId)->willReturn($box);
        $this->experienceRepository->findOneByGoldenId($boxExperienceDeleteRequest->experienceGoldenId)->willReturn($experience);

        $this->repository->findOneByBoxExperience($box, $experience)->willReturn(null);

        $this->repository->delete($boxExperience)->shouldNotBeCalled();

        $manager->delete($boxExperienceDeleteRequest);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreate()
    {
        $manager = new BoxExperienceManager(
            $this->repository->reveal(),
            $this->boxRepository->reveal(),
            $this->experienceRepository->reveal(),
            $this->manageableProductService->reveal(),
            $this->boxManager->reveal(),
        );

        $box = new Box();
        $box->goldenId = '5678';
        $this->boxRepository->findOneByGoldenId('5678')->willReturn($box);

        $experience = new Experience();
        $experience->goldenId = '9012';
        $this->experienceRepository->findOneByGoldenId('9012')->willReturn($experience);

        $currentDate = new \DateTime();
        $bookingCreateRequest = new BoxExperienceCreateRequest();

        $bookingCreateRequest->boxGoldenId = '5678';
        $bookingCreateRequest->experienceGoldenId = '9012';
        $bookingCreateRequest->externalUpdatedAt = $currentDate;
        $bookingCreateRequest->isEnabled = true;

        $this->repository->findOneByBoxExperience($box, $experience)->willReturn(null);
        $this->repository->save(Argument::type(BoxExperience::class))->shouldBeCalled();

        $booking = $manager->create($bookingCreateRequest);
        $this->assertEquals($bookingCreateRequest->boxGoldenId, $booking->boxGoldenId);
        $this->assertEquals($bookingCreateRequest->experienceGoldenId, $booking->experienceGoldenId);
        $this->assertEquals($bookingCreateRequest->externalUpdatedAt, $booking->externalUpdatedAt);
        $this->assertEquals($bookingCreateRequest->isEnabled, $booking->isEnabled);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreateDuplicate()
    {
        $manager = new BoxExperienceManager(
            $this->repository->reveal(),
            $this->boxRepository->reveal(),
            $this->experienceRepository->reveal(),
            $this->manageableProductService->reveal(),
            $this->boxManager->reveal(),
        );

        $box = new Box();
        $box->goldenId = '5678';
        $this->boxRepository->findOneByGoldenId('5678')->willReturn($box);

        $experience = new Experience();
        $experience->goldenId = '9012';
        $this->experienceRepository->findOneByGoldenId('9012')->willReturn($experience);

        $currentDate = new \DateTime();
        $bookingCreateRequest = new BoxExperienceCreateRequest();

        $bookingCreateRequest->boxGoldenId = '5678';
        $bookingCreateRequest->experienceGoldenId = '9012';
        $bookingCreateRequest->externalUpdatedAt = $currentDate;

        $this->expectException(RelationshipAlreadyExistsException::class);
        $this->repository->findOneByBoxExperience($box, $experience)->willReturn(new BoxExperience());
        $booking = $manager->create($bookingCreateRequest);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreateWithInvalidBoxGoldenId()
    {
        $manager = new BoxExperienceManager(
            $this->repository->reveal(),
            $this->boxRepository->reveal(),
            $this->experienceRepository->reveal(),
            $this->manageableProductService->reveal(),
            $this->boxManager->reveal(),
        );
        $this->boxRepository->findOneByGoldenId('5678')->willThrow(BoxNotFoundException::class);

        $bookingCreateRequest = new BoxExperienceCreateRequest();
        $bookingCreateRequest->boxGoldenId = '5678';

        $this->expectException(BoxNotFoundException::class);
        $manager->create($bookingCreateRequest);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreateWithInvalidExperienceGoldenId()
    {
        $manager = new BoxExperienceManager(
            $this->repository->reveal(),
            $this->boxRepository->reveal(),
            $this->experienceRepository->reveal(),
            $this->manageableProductService->reveal(),
            $this->boxManager->reveal(),
        );

        $box = new Box();
        $box->goldenId = '5678';
        $this->boxRepository->findOneByGoldenId('5678')->willReturn($box);

        $this->experienceRepository->findOneByGoldenId('9012')->willThrow(ExperienceNotFoundException::class);

        $bookingCreateRequest = new BoxExperienceCreateRequest();

        $bookingCreateRequest->boxGoldenId = '5678';
        $bookingCreateRequest->experienceGoldenId = '9012';

        $this->expectException(ExperienceNotFoundException::class);
        $manager->create($bookingCreateRequest);
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplace()
    {
        $manager = new BoxExperienceManager(
            $this->repository->reveal(),
            $this->boxRepository->reveal(),
            $this->experienceRepository->reveal(),
            $this->manageableProductService->reveal(),
            $this->boxManager->reveal(),
        );

        $box = new Box();
        $box->goldenId = '1234';
        $this->boxRepository->findOneByGoldenId('1234')->willReturn($box);

        $experience = new Experience();
        $experience->goldenId = '7895';
        $this->experienceRepository->findOneByGoldenId('7895')->willReturn($experience);

        $relationshipRequest = new ProductRelationshipRequest();
        $relationshipRequest->parentProduct = '1234';
        $relationshipRequest->childProduct = '7895';
        $relationshipRequest->isEnabled = false;
        $relationshipRequest->updatedAt = new \DateTime('2020-01-01 01:00:00');

        $boxExperience = new BoxExperience();
        $boxExperience->boxGoldenId = '1234';
        $boxExperience->experienceGoldenId = '7895';
        $boxExperience->isEnabled = true;
        $boxExperience->externalUpdatedAt = new \DateTime('2020-01-01 00:00:00');
        $this->repository->findOneByBoxExperience($box, $experience)->willReturn($boxExperience);
        $this->manageableProductService->dispatchForProductRelationship(Argument::any())->shouldBeCalled();
        $this->repository->save(Argument::type(BoxExperience::class))->shouldBeCalled();

        $updatedBoxExperience = $manager->replace($relationshipRequest);

        $this->assertSame(null, $updatedBoxExperience);
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplaceWithPlaceholderBox()
    {
        $manager = new BoxExperienceManager(
            $this->repository->reveal(),
            $this->boxRepository->reveal(),
            $this->experienceRepository->reveal(),
            $this->manageableProductService->reveal(),
            $this->boxManager->reveal(),
        );

        $box = new Box();
        $box->goldenId = '1234';
        $this->boxRepository->findOneByGoldenId('1234')->willThrow(new BoxNotFoundException());
        $this->boxManager->createPlaceholder('1234')->shouldBeCalled()->willReturn($box);

        $experience = new Experience();
        $experience->goldenId = '7895';
        $this->experienceRepository->findOneByGoldenId('7895')->willReturn($experience);

        $relationshipRequest = new ProductRelationshipRequest();
        $relationshipRequest->parentProduct = '1234';
        $relationshipRequest->childProduct = '7895';
        $relationshipRequest->isEnabled = false;
        $relationshipRequest->updatedAt = new \DateTime('2020-01-01 01:00:00');

        $boxExperience = new BoxExperience();
        $boxExperience->boxGoldenId = '1234';
        $boxExperience->experienceGoldenId = '7895';
        $boxExperience->isEnabled = true;
        $boxExperience->externalUpdatedAt = new \DateTime('2020-01-01 00:00:00');
        $this->repository->findOneByBoxExperience($box, $experience)->willReturn($boxExperience);
        $this->manageableProductService->dispatchForProductRelationship(Argument::any())->shouldBeCalled();
        $this->repository->save(Argument::type(BoxExperience::class))->shouldBeCalled();

        $updatedBoxExperience = $manager->replace($relationshipRequest);

        $this->assertSame(null, $updatedBoxExperience);
    }

    /**
     * @covers ::__construct
     * @covers ::replace
     */
    public function testReplaceWithOutdatedRecord()
    {
        $manager = new BoxExperienceManager(
            $this->repository->reveal(),
            $this->boxRepository->reveal(),
            $this->experienceRepository->reveal(),
            $this->manageableProductService->reveal(),
            $this->boxManager->reveal(),
        );

        $box = new Box();
        $box->goldenId = '1234';
        $this->boxRepository->findOneByGoldenId('1234')->willReturn($box);

        $experience = new Experience();
        $experience->goldenId = '7895';
        $this->experienceRepository->findOneByGoldenId('7895')->willReturn($experience);

        $date = new \DateTime();
        $relationshipRequest = new ProductRelationshipRequest();
        $relationshipRequest->parentProduct = '1234';
        $relationshipRequest->childProduct = '7895';
        $relationshipRequest->isEnabled = false;
        $relationshipRequest->updatedAt = new \DateTime('2020-01-01 00:00:00');

        $boxExperience = new BoxExperience();
        $boxExperience->externalUpdatedAt = new \DateTime('2020-01-01 01:00:00');
        $this->repository->findOneByBoxExperience($box, $experience)->willReturn($boxExperience);

        $this->expectException(OutdatedBoxExperienceRelationshipException::class);
        $manager->replace($relationshipRequest);
    }
}
