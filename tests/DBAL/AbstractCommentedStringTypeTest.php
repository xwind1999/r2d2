<?php

declare(strict_types=1);

namespace App\Tests\DBAL;

use App\DBAL\AbstractCommentedStringType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;

class AbstractCommentedStringTypeTest extends TestCase
{
    public function testRequiresSQLCommentHint()
    {
        $instance = new class() extends AbstractCommentedStringType {
        };

        $this->assertTrue($instance->requiresSQLCommentHint($this->prophesize(AbstractPlatform::class)->reveal()));
    }
}
