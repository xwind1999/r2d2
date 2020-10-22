<?php

declare(strict_types=1);

namespace App\Helper;

use App\Exception\Helper\InvalidCSVHeadersException;
use App\Tests\ProphecyKernelTestCase;

/**
 * @coversDefaultClass \App\Helper\CSVParser
 */
class CSVParserTest extends ProphecyKernelTestCase
{
    private $tmpFile;

    private array $expected = [
        ['parentProduct', 'childProduct', 'isEnabled', 'relationshipType'],
        ['189970', '482341', '1', 'Experience-Component'],
        ['108338', '321805', '1', 'Experience-Component'],
        ['73026', '237127', '1', 'Experience-Component'],
    ];

    public function setUp(): void
    {
        $this->tmpFile = tmpfile();
        foreach ($this->expected as $row) {
            fputcsv($this->tmpFile, $row);
        }
        rewind($this->tmpFile);
    }

    public function tearDown(): void
    {
        fclose($this->tmpFile);
    }

    /**
     * @covers ::readFile
     */
    public function testReadFile(): void
    {
        $csvParser = new CSVParser();

        $fields = [
            'parentProduct',
            'childProduct',
            'isEnabled',
            'relationshipType',
        ];
        $response = $csvParser->readFile(stream_get_meta_data($this->tmpFile)['uri'], $fields);

        $this->assertInstanceOf(\Iterator::class, $response);
    }

    /**
     * @covers ::readFile
     */
    public function testReadFileWithInvalidHeaders(): void
    {
        $csvParser = new CSVParser();

        $fields = [
            'parentProduct',
            'childProduct',
            'isEnabled',
            'relationshipType',
            'invalidHeader',
        ];

        $this->expectException(InvalidCSVHeadersException::class);
        $response = $csvParser->readFile(stream_get_meta_data($this->tmpFile)['uri'], $fields);
    }
}
