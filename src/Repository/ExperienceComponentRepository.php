<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Experience;
use App\Entity\ExperienceComponent;
use App\Entity\Room;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method null|ExperienceComponent find($id, $lockMode = null, $lockVersion = null)
 * @method null|ExperienceComponent findOneBy(array $criteria, array $orderBy = null)
 * @method ExperienceComponent[]    findAll()
 * @method ExperienceComponent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperienceComponentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExperienceComponent::class);
    }

    public function save(ExperienceComponent $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function delete(ExperienceComponent $entity): void
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }

    public function findOneByExperienceComponent(Experience $experience, Room $room): ?ExperienceComponent
    {
        return $this->findOneBy(['experience' => $experience, 'room' => $room]);
    }
}
