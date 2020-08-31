<?php

declare(strict_types=1);

namespace App\Tests\Contract\Message;

use App\Contract\Message\CalculateFlatManageableComponent;
use PHPUnit\Framework\TestCase;

class CalculateFlatManageableComponentTest extends TestCase
{
    public function testGetContext()
    {
        $event = new CalculateFlatManageableComponent('1234');
        $this->assertEquals(['component_golden_id' => '1234'], $event->getContext());
    }

    public function testGetEventName()
    {
        $event = new CalculateFlatManageableComponent('1234');
        $this->assertEquals('Calculate flat manageable component', $event->getEventName());
    }
}
