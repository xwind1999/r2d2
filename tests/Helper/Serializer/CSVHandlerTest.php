<?php

declare(strict_types=1);

namespace App\Tests\Helper\Serializer;

use App\Helper\Serializer\CSVHandler;
use App\Tests\ProphecyTestCase;
use JMS\Serializer\Context;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;

class CSVHandlerTest extends ProphecyTestCase
{
    public function testDeserializeCSVFromJSON()
    {
        $visitor = $this->prophesize(DeserializationVisitorInterface::class);
        $csvHandler = new CSVHandler();
        $this->assertEquals([1, 2, 3], $csvHandler->deserializeCSVFromJSON($visitor->reveal(), '1,2,3', []));
    }

    public function testGetSubscribingMethods()
    {
        $this->assertIsArray(CSVHandler::getSubscribingMethods());
    }

    public function testSerializeCSVToJSON()
    {
        $visitor = $this->prophesize(SerializationVisitorInterface::class);
        $context = $this->prophesize(Context::class);
        $csvHandler = new CSVHandler();
        $visitor->visitBoolean(false, [])->willReturn(false);
        $this->assertEquals('1,2,3', $csvHandler->serializeCSVToJSON($visitor->reveal(), [1, 2, 3], [], $context->reveal()));
    }
}
