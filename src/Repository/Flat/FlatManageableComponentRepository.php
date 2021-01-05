<?php

declare(strict_types=1);

namespace App\Repository\Flat;

use App\Entity\Flat\FlatManageableComponent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

class FlatManageableComponentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FlatManageableComponent::class);
    }

    public function refreshComponent(string $componentGoldenId): void
    {
        $this->getEntityManager()->getConnection()->transactional(function (Connection $conn) use ($componentGoldenId) {
            $sql = <<<SQL
DELETE FROM flat_manageable_component WHERE component_golden_id = :componentGoldenId;

INSERT INTO
    flat_manageable_component
    (box_golden_id, experience_golden_id, component_golden_id, component_uuid, partner_golden_id, duration, is_sellable, room_stock_type, last_bookable_date) 
SELECT
       box_golden_id,
       be.experience_golden_id,
       component_golden_id,
       c.uuid,
       c.partner_golden_id,
       c.duration,
       c.is_sellable,
       c.room_stock_type,
       IF(ISNULL(p.cease_date), null, DATE_SUB(p.cease_date, interval c.duration day))
FROM box_experience be
    JOIN experience_component ec 
        ON be.experience_uuid = ec.experience_uuid  AND ec.component_golden_id = :componentGoldenId AND be.is_enabled = 1 AND ec.is_enabled = 1
    JOIN component c 
        ON c.uuid = ec.component_uuid AND c.is_manageable = 1 AND c.duration > 0
    JOIN partner p 
        ON p.uuid = c.partner_uuid
;

SQL;
            $statement = $conn->prepare($sql);
            $statement->bindValue('componentGoldenId', $componentGoldenId);
            $statement->execute();
        });
    }

    public function getBoxesByComponentId(string $componentGoldenId): array
    {
        $sql = 'SELECT DISTINCT box_golden_id FROM flat_manageable_component WHERE component_golden_id = :componentGoldenId';

        $statement = $this->getEntityManager()->getConnection()->prepare($sql);
        $statement->bindValue('componentGoldenId', $componentGoldenId);
        $statement->execute();

        return array_column($statement->fetchAllAssociative(), 'box_golden_id');
    }
}
