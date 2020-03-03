<?php

declare(strict_types=1);

namespace App\Helper\Serializer;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;

class StrictBooleanHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => 'strict_boolean',
                'method' => 'deserializeStrictBooleanFromJSON',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'strict_boolean',
                'method' => 'serializeStrictBooleanToJSON',
            ],
        ];
    }

    public function deserializeStrictBooleanFromJSON(DeserializationVisitorInterface $visitor, bool $data, array $type): bool
    {
        return $data;
    }

    /**
     * @return mixed
     */
    public function serializeStrictBooleanToJSON(SerializationVisitorInterface $visitor, bool $data, array $type, Context $context)
    {
        return $visitor->visitBoolean($data, $type);
    }
}
