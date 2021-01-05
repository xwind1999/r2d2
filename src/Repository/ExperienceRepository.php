<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Experience;
use App\Exception\Repository\ExperienceNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

    public function save(Experience $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function delete(Experience $entity): void
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }

    public function findOne(string $uuid): Experience
    {
        $experience = $this->find($uuid);

        if (null === $experience) {
            throw new ExperienceNotFoundException();
        }

        return $experience;
    }

    public function findOneByGoldenId(string $goldenId): Experience
    {
        $experience = $this->findOneBy(['goldenId' => $goldenId]);

        if (null === $experience) {
            throw new ExperienceNotFoundException();
        }

        return $experience;
    }
}
