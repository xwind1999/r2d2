<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table
 */
class Calendar
{
    /**
     * @ORM\Id
     * @ORM\Column(type="date")
     */
    public \DateTime $date;
}
