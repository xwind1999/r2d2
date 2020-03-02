<?php

declare(strict_types=1);

namespace App\Helper\Serializer;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;

class StrictIntegerHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => 'strict_integer',
                'method' => 'deserializeStrictIntegerFromJSON',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'strict_integer',
                'method' => 'serializeStrictIntegerToJSON',
            ],
        ];
    }

    public function deserializeStrictIntegerFromJSON(DeserializationVisitorInterface $visitor, int $data, array $type): int
    {
        return $data;
    }

    /**
     * @return mixed
     */
    public function serializeStrictIntegerToJSON(SerializationVisitorInterface $visitor, int $data, array $type, Context $context)
    {
        return $visitor->visitInteger($data, $type);
    }
}
