<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Experience;
use App\Exception\Repository\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method null|Experience find($id, $lockMode = null, $lockVersion = null)
 * @method null|Experience findOneBy(array $criteria, array $orderBy = null)
 * @method Experience[]    findAll()
 * @method Experience[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperienceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Experience::class);
    }

    public function findOne(string $uuid): Experience
    {
        $experience = $this->find($uuid);

        if (null === $experience) {
            throw new EntityNotFoundException();
        }

        return $experience;
    }
}
