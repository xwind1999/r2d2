<?php

declare(strict_types=1);

namespace App\Tests\Helper\Serializer;

use App\Helper\Serializer\StrictIntegerHandler;
use JMS\Serializer\Context;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use PHPUnit\Framework\TestCase;

class StrictIntegerHandlerTest extends TestCase
{
    public function testDeserializeStrictIntegerFromJSON()
    {
        $visitor = $this->prophesize(DeserializationVisitorInterface::class);
        $strictIntegerHandler = new StrictIntegerHandler();
        $this->assertSame(12, $strictIntegerHandler->deserializeStrictIntegerFromJSON($visitor->reveal(), 12, []));
    }

    public function testGetSubscribingMethods()
    {
        $this->assertIsArray(StrictIntegerHandler::getSubscribingMethods());
    }

    public function testSerializeStrictIntegerToJSON()
    {
        $visitor = $this->prophesize(SerializationVisitorInterface::class);
        $context = $this->prophesize(Context::class);
        $strictIntegerHandler = new StrictIntegerHandler();
        $visitor->visitInteger(12, [])->willReturn(12);
        $this->assertSame(12, $strictIntegerHandler->serializeStrictIntegerToJSON($visitor->reveal(), 12, [], $context->reveal()));
    }
}
