<?php

declare(strict_types=1);

namespace App\Logger\Processor;

class AppNameProcessor
{
    private const APP_NAME = 'r2-d2';

    public function addInfo(array $record): array
    {
        $record['extra']['syslog5424_app'] = self::APP_NAME;

        return $record;
    }
}
