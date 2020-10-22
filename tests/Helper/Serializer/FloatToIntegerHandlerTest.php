<?php

declare(strict_types=1);

namespace App\Tests\Helper\Serializer;

use App\Helper\Serializer\FloatToIntegerHandler;
use App\Tests\ProphecyTestCase;
use JMS\Serializer\Context;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;

/**
 * @covers \App\Helper\Serializer\FloatToIntegerHandler
 */
class FloatToIntegerHandlerTest extends ProphecyTestCase
{
    public function testDeserializeFloatToIntegerFromJSON()
    {
        $visitor = $this->prophesize(DeserializationVisitorInterface::class);
        $strictBooleanHandler = new FloatToIntegerHandler();
        $this->assertSame(5556, $strictBooleanHandler->deserializeFloatToIntegerFromJSON($visitor->reveal(), 55.56, []));
    }

    public function testGetSubscribingMethods()
    {
        $this->assertIsArray(FloatToIntegerHandler::getSubscribingMethods());
    }

    public function testSerializeFloatToIntegerToJSON()
    {
        $visitor = $this->prophesize(SerializationVisitorInterface::class);
        $context = $this->prophesize(Context::class);
        $strictBooleanHandler = new FloatToIntegerHandler();
        $visitor->visitDouble(55.56, [])->willReturn(55.56);
        $this->assertEquals(
            55.56,
            $strictBooleanHandler->serializeFloatToIntegerToJSON($visitor->reveal(), 5556, [], $context->reveal())
        );
    }
}
