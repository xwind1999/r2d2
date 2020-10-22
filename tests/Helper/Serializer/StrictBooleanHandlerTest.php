<?php

declare(strict_types=1);

namespace App\Tests\Helper\Serializer;

use App\Helper\Serializer\StrictBooleanHandler;
use App\Tests\ProphecyTestCase;
use JMS\Serializer\Context;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;

class StrictBooleanHandlerTest extends ProphecyTestCase
{
    public function testDeserializeStrictBooleanFromJSON()
    {
        $visitor = $this->prophesize(DeserializationVisitorInterface::class);
        $strictBooleanHandler = new StrictBooleanHandler();
        $this->assertSame(true, $strictBooleanHandler->deserializeStrictBooleanFromJSON($visitor->reveal(), true, []));
    }

    public function testGetSubscribingMethods()
    {
        $this->assertIsArray(StrictBooleanHandler::getSubscribingMethods());
    }

    public function testSerializeStrictBooleanToJSON()
    {
        $visitor = $this->prophesize(SerializationVisitorInterface::class);
        $context = $this->prophesize(Context::class);
        $strictBooleanHandler = new StrictBooleanHandler();
        $visitor->visitBoolean(false, [])->willReturn(false);
        $this->assertSame(false, $strictBooleanHandler->serializeStrictBooleanToJSON($visitor->reveal(), false, [], $context->reveal()));
    }
}
