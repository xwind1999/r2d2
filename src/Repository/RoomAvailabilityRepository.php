<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RoomAvailability;
use App\Exception\Repository\RoomAvailabilityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method null|RoomAvailability find($id, $lockMode = null, $lockVersion = null)
 * @method null|RoomAvailability findOneBy(array $criteria, array $orderBy = null)
 * @method RoomAvailability[]    findAll()
 * @method RoomAvailability[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoomAvailabilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoomAvailability::class);
    }

    public function save(RoomAvailability $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function delete(RoomAvailability $entity): void
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }

    public function findOne(string $uuid): RoomAvailability
    {
        $roomAvailability = $this->find($uuid);

        if (null === $roomAvailability) {
            throw new RoomAvailabilityNotFoundException();
        }

        return $roomAvailability;
    }

    public function findRoomAvailabilitiesByComponentGoldenIds(array $componentIds, string $type, \DateTimeInterface $dateFrom, \DateTimeInterface $dateTo): array
    {
        $numberOfNights = $dateTo->diff($dateFrom)->days ?: 0;
        $qb = $this->createQueryBuilder('r');
        $qb
            ->select('r.componentGoldenId')
            ->where($qb->expr()->in('r.componentGoldenId', $componentIds))
            ->andWhere('r.date BETWEEN :dateFrom AND :dateTo')
            ->andWhere('r.type = :type')
            ->andWhere('r.stock > 0')
            ->groupBy('r.componentGoldenId')
            ->having('count(r.date) = :numberOfDays')
            ->setParameter('dateFrom', $dateFrom->format('Y-m-d'))
            ->setParameter('dateTo', $dateTo->format('Y-m-d'))
            ->setParameter('numberOfDays', $numberOfNights + 1)
            ->setParameter('type', $type)
            ->indexBy('r', 'r.componentGoldenId')
        ;

        return $qb->getQuery()->getArrayResult();
    }
}
