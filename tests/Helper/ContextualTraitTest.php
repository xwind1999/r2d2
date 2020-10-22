<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Helper\ContextualTrait;
use App\Tests\ProphecyTestCase;

/**
 * @coversDefaultClass \App\Helper\ContextualTrait
 */
class ContextualTraitTest extends ProphecyTestCase
{
    /**
     * @covers ::getContext
     * @covers ::setContext
     * @covers ::addContext
     */
    public function testTrait()
    {
        $uses = new class() {
            use ContextualTrait;
        };
        $context = ['a' => 'b'];
        $context2 = ['c' => 'd'];
        $uses->setContext($context);
        $this->assertEquals($context, $uses->getContext());
        $uses->addContext($context2);
        $this->assertEquals($context + $context2, $uses->getContext());
    }
}
