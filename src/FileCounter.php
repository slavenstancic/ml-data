<?php

namespace App;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FileCounter
{
    private const DIR = __DIR__ . '/../documents/';
    private const DATA = 'Data';

    /** @var int Max rows to read */
    private const MAX_ROWS = 100;

    /** @var CellParserInterface[] */
    private $cellParsers;

    /** @var bool */
    private $perRow;

    /** @var array */
    private $alphabet;

    /**
     * FileCounter constructor.
     *
     * @param array $cellParsers
     * @param bool  $perRow
     *
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function __construct(array $cellParsers, bool $perRow = false)
    {
        $this->cellParsers = $cellParsers;
        $this->perRow = $perRow;
        $this->alphabet = range('A', 'Z');
        $this->countAllFilesData();
    }

    /**
     * Get result
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws Exception
     */
    public function countAllFilesData(): void
    {
        $spreadsheet = new Spreadsheet();
        $alphabet = range('A', 'Z');
        $data = $this->iterateFiles();
        $sheetIndex = 0;
        $head = [];

        foreach ($data as $file => $row) {
            $newSheet = new Worksheet($spreadsheet, substr($file, 0, 28));
            $spreadsheet->addSheet($newSheet, $sheetIndex);
            $spreadsheet->setActiveSheetIndex($sheetIndex);
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);
            $spreadsheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(15);

            $rowNumber = 2;

            foreach ($row as $rowNum => $column) {
                foreach ($column as $columnNum => $data) {
                    $head = array_keys($data);
                    $index = 0;

                    foreach ($data as $type => $value) {
                        $column = $alphabet[$index] . $rowNumber;
                        $spreadsheet->getActiveSheet()->setCellValue($column, $value);
                        $index++;
                    }

                    $rowNumber++;
                }
            }

            foreach ($head as $key => $title) {
                $spreadsheet->getActiveSheet()->setCellValue($alphabet[$key] . 1, $title);
            }

            $sheetIndex++;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('results/result.xlsx');
    }

    /**
     * Get data of all files in dir
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws Exception
     */
    private function iterateFiles(): array
    {
        $files = array_diff(scandir(self::DIR), ['..', '.', '.gitkeep']);
        $return = [];
        foreach ($files as $file) {
            $filePath = self::DIR . $file;
            $inputFileType = IOFactory::identify($filePath);
            $reader = IOFactory::createReader($inputFileType);
            $reader->setReadDataOnly(true);

            $spreadsheet = $reader->load($filePath);
            $content = $spreadsheet->getActiveSheet();
            $result = $this->calculateData($content);

            $return[$file] = $result;
        }

        return $return;
    }

    /**
     * Get calculation of characters
     *
     * @param Worksheet $data
     *
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function calculateData(Worksheet $data): array
    {
        $result = [];
        foreach ($data->getRowIterator(1, self::MAX_ROWS) as $row) {
            $cells = $row->getCellIterator();
            try {
                $cells->setIterateOnlyExistingCells(true);
            } catch (\Exception $e) {
                break;
            }
            $row = $row->getRowIndex();
            $rowVal = '';

            foreach ($cells as $cell) {
                $value = $cell->getValue();

                if ($this->perRow) {
                    $rowVal .= $value;
                    continue;
                }

                $cell = $cells->key();

                $result[$row][$cell][self::DATA] = $value;

                foreach ($this->cellParsers as $cellParser) {
                    $result[$row][$cell][$cellParser->getName()] = $cellParser->getValue($value);
                }
            }

            if ($this->perRow) {
                foreach ($this->cellParsers as $cellParser) {
                    $result[$row]['A'][self::DATA] = $rowVal;
                    $result[$row]['A'][$cellParser->getName()] = $cellParser->getValue($rowVal);
                }
            }
        }

        return $result;
    }
}
