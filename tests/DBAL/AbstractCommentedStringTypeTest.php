<?php

declare(strict_types=1);

namespace App\Tests\DBAL;

use App\DBAL\AbstractCommentedStringType;
use App\Tests\ProphecyTestCase;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class AbstractCommentedStringTypeTest extends ProphecyTestCase
{
    public function testRequiresSQLCommentHint()
    {
        $instance = new class() extends AbstractCommentedStringType {
        };

        $this->assertTrue($instance->requiresSQLCommentHint($this->prophesize(AbstractPlatform::class)->reveal()));
    }
}
