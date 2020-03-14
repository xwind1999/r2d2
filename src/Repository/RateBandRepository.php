<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RateBand;
use App\Exception\Repository\RateBandNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method null|RateBand find($id, $lockMode = null, $lockVersion = null)
 * @method null|RateBand findOneBy(array $criteria, array $orderBy = null)
 * @method RateBand[]    findAll()
 * @method RateBand[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RateBandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RateBand::class);
    }

    public function save(RateBand $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function delete(RateBand $entity): void
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }

    public function findOne(string $uuid): RateBand
    {
        $rateBand = $this->find($uuid);

        if (null === $rateBand) {
            throw new RateBandNotFoundException();
        }

        return $rateBand;
    }

    public function findOneByGoldenId(string $goldenId): RateBand
    {
        $rateBand = $this->findOneBy(['goldenId' => $goldenId]);

        if (null === $rateBand) {
            throw new RateBandNotFoundException();
        }

        return $rateBand;
    }
}
