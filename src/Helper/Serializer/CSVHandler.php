<?php

declare(strict_types=1);

namespace App\Helper\Serializer;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;

class CSVHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => 'csv',
                'method' => 'deserializeCSVFromJSON',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'csv',
                'method' => 'serializeCSVToJSON',
            ],
        ];
    }

    public function deserializeCSVFromJSON(DeserializationVisitorInterface $visitor, string $data, array $type): array
    {
        return str_getcsv($data);
    }

    /**
     * @return mixed
     */
    public function serializeCSVToJSON(SerializationVisitorInterface $visitor, array $data, array $type, Context $context)
    {
        return implode(',', $data);
    }
}
