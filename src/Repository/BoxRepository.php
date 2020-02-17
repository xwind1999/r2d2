<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Box;
use App\Exception\Repository\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method null|Box find($id, $lockMode = null, $lockVersion = null)
 * @method null|Box findOneBy(array $criteria, array $orderBy = null)
 * @method Box[]    findAll()
 * @method Box[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BoxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Box::class);
    }

    public function findOne(string $uuid): Box
    {
        $box = $this->find($uuid);

        if (null === $box) {
            throw new EntityNotFoundException();
        }

        return $box;
    }
}
