<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Box;
use App\Entity\BoxExperience;
use App\Entity\Experience;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method null|BoxExperience find($id, $lockMode = null, $lockVersion = null)
 * @method null|BoxExperience findOneBy(array $criteria, array $orderBy = null)
 * @method BoxExperience[]    findAll()
 * @method BoxExperience[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BoxExperienceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BoxExperience::class);
    }

    public function save(BoxExperience $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function delete(BoxExperience $entity): void
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }

    public function findOneByBoxExperience(Box $box, Experience $experience): ?BoxExperience
    {
        return $this->findOneBy(['box' => $box, 'experience' => $experience]);
    }
}
