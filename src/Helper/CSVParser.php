<?php

declare(strict_types=1);

namespace App\Helper;

use App\Exception\Helper\InvalidCSVHeadersException;
use League\Csv\Reader;

class CSVParser
{
    private const HEADER_OFFSET = 0;

    public function readFile(string $filePath, array $fields): \Iterator
    {
        $reader = Reader::createFromPath($filePath);
        $reader->setHeaderOffset(self::HEADER_OFFSET);

        if ($reader->getHeader() !== $fields) {
            throw new InvalidCSVHeadersException();
        }

        return $reader->getRecords($fields);
    }
}
