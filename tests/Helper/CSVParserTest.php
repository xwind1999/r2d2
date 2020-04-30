<?php

declare(strict_types=1);

namespace App\Tests\Command\Import\Helper;

use App\Helper\CSVParser;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @coversDefaultClass \App\Helper\CSVParser
 */
class CSVParserTest extends KernelTestCase
{
    private Reader $csv;

    private array $expected = [
        ['parentProduct', 'childProduct', 'sortOrder', 'isEnabled', 'relationshipType', 'childCount', 'childQuantity'],
        ['189970', '482341', '1', '1', 'Experience-Component', '0', '0'],
        ['108338', '321805', '1', '1', 'Experience-Component', '0', '0'],
        ['73026', '237127', '1', '1', 'Experience-Component', '0', '0'],
    ];

    public function setUp(): void
    {
        $tmp = new \SplTempFileObject();
        foreach ($this->expected as $row) {
            $tmp->fputcsv($row);
        }

        $this->csv = Reader::createFromFileObject($tmp);
    }

    public function tearDown(): void
    {
        unset($this->csv);
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
            'sortOrder',
            'isEnabled',
            'relationshipType',
            'printType',
            'childCount',
            'childQuantity',
        ];

        $response = $csvParser->readFile($this->csv->getPathname(), $fields);

        $this->assertInstanceOf(\Iterator::class, $response);
    }
}
