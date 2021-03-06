<?php

declare(strict_types=1);

namespace App\Repository;

use App\Constants\DateTimeConstants;
use App\Entity\Component;
use App\Entity\RoomPrice;
use App\Exception\Repository\RoomPriceNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RoomPrice[]    findAll()
 * @method RoomPrice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|RoomPrice find($id, $lockMode = null, $lockVersion = null)
 * @method null|RoomPrice findOneBy(array $criteria, array $orderBy = null)
 */
class RoomPriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoomPrice::class);
    }

    public function save(RoomPrice $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function delete(RoomPrice $entity): void
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }

    public function findOne(string $uuid): RoomPrice
    {
        $roomPrice = $this->find($uuid);

        if (null === $roomPrice) {
            throw new RoomPriceNotFoundException();
        }

        return $roomPrice;
    }

    /**
     * @return RoomPrice[]
     */
    public function findByComponentAndDateRange(Component $component, \DateTimeInterface $dateFrom, \DateTimeInterface $dateTo): array
    {
        $qb = $this->createQueryBuilder('rp');
        $qb
            ->where('rp.component = :component')
            ->andWhere('rp.date BETWEEN :dateFrom AND :dateTo')
            ->setParameter('component', $component->uuid->getBytes())
            ->setParameter('dateFrom', $dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT))
            ->setParameter('dateTo', $dateTo->format(DateTimeConstants::DEFAULT_DATE_FORMAT))
            ->indexBy('rp', 'rp.date')
        ;

        return $qb->getQuery()->getResult();
    }
}
