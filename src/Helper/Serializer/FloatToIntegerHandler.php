<?php

declare(strict_types=1);

namespace App\Helper\Serializer;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;

class FloatToIntegerHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => 'float_to_integer',
                'method' => 'deserializeFloatToIntegerFromJSON',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'float_to_integer',
                'method' => 'serializeFloatToIntegerToJSON',
            ],
        ];
    }

    public function deserializeFloatToIntegerFromJSON(DeserializationVisitorInterface $visitor, float $data, array $type): int
    {
        return (int) ($data * 100);
    }

    /**
     * @return mixed
     */
    public function serializeFloatToIntegerToJSON(SerializationVisitorInterface $visitor, int $data, array $type, Context $context)
    {
        $data /= 100;

        return $visitor->visitDouble($data, $type);
    }
}
