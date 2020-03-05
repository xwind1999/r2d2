<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Partner;
use App\Exception\Repository\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method null|Partner find($id, $lockMode = null, $lockVersion = null)
 * @method null|Partner findOneBy(array $criteria, array $orderBy = null)
 * @method Partner[]    findAll()
 * @method Partner[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PartnerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Partner::class);
    }

    public function findOne(string $uuid): Partner
    {
        $partner = $this->find($uuid);

        if (null === $partner) {
            throw new EntityNotFoundException();
        }

        return $partner;
    }
}
