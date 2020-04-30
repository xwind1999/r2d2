<?php

declare(strict_types=1);

namespace App\Helper;

use League\Csv\Reader;

class CSVParser
{
    public function readFile(string $filePath, array $fields): \Iterator
    {
        $reader = Reader::createFromPath($filePath);
        $reader->setHeaderOffset(0);

        return $reader->getRecords($fields);
    }
}
