<?php

namespace App;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FileCounter
{
    /** @var string */
    private const DIR = __DIR__ . '/../documents/';

    /** @var array */
    private const CHARACTERS = ['/', ':', ',', '.', ' '];

    private const DATA = 'Data';
    private const DIGITS = '# of digits';
    private const WORDS = '# of words';
    private const CHARS = '# of characters';

    /** @var int Max rows to read */
    private const MAX_ROWS = 100;

    /**
     * FileCounter constructor.
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws Exception
     */
    public function __construct()
    {
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
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
            $spreadsheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(15);

            foreach ($row as $rowNum => $column) {
                foreach ($column as $columnNum => $data) {
                    $head = array_keys($data);
                    $index = 0;

                    foreach ($data as $type => $value) {
                        $column = $alphabet[$index] . ($rowNum + 1);
                        $spreadsheet->getActiveSheet()->setCellValue($column, $value);
                        $index++;
                    }
                }
            }

            foreach ($head as $key => $title) {
                $spreadsheet->getActiveSheet()->setCellValue($alphabet[$key] . 1, $title);
            }

            $sheetIndex++;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save("results/result.xlsx");
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
            $content = $spreadsheet->getActiveSheet()->toArray();
            $result = $this->calculateData($content);

            $return[$file] = $result;
        }

        return $return;
    }

    /**
     * Get calculation of characters
     * @param array $data
     * @return array
     */
    private function calculateData(array $data): array
    {
        $result = [];
        foreach ($data as $row => $cells) {
            if ($row > self::MAX_ROWS) {
                break;
            }

            foreach ($cells as $cell => $value) {
                $result[$row][$cell][self::DATA] = $value;

                foreach (self::CHARACTERS as $character) {
                    $result[$row][$cell][$character] = substr_count($value, $character);
                }

                $result[$row][$cell][self::DIGITS] = preg_match_all("/[0-9]/", $value);
                $result[$row][$cell][self::WORDS] = preg_match_all("/[A-Z]/", $value);
                $result[$row][$cell][self::CHARS] = strlen($value);

                $result[$row][$cell]['Is date'] = $this->checkDate($result[$row][$cell]);
                $result[$row][$cell]['Is time'] = $this->checkTime($result[$row][$cell]);
                $result[$row][$cell]['Is number'] = $this->checkNumber($result[$row][$cell]);

            }
        }

        return $result;
    }

    /**
     * Check if it is date format
     * @param array $data
     * @return string
     */
    private function checkDate(array $data): string
    {
        if ($data[self::DIGITS] >= 4
            && $data[self::WORDS] === 0
            && ((isset($data['.']) && $data['.'] > 1) || (isset($data['/']) && $data['/'] > 1))
        ) {
            return 'Yes';
        }

        return 'No';
    }

    /**
     * Check if it is time format
     * @param array $data
     * @return string
     */
    private function checkTime(array $data): string
    {
        if ($data[self::DIGITS] >= 4
            && $data[self::WORDS] === 0
            && ((isset($data[':']) && $data[':'] > 1) || (isset($data['.']) && $data['.'] > 1))
        ) {
            return 'Yes';
        }

        return 'No';
    }

    /**
     * Check if it is numeric format
     * @param array $data
     * @return string
     */
    private function checkNumber(array $data): string
    {
        if ($data[self::DIGITS] > 0
            && $data[self::WORDS] === 0
            && (
                (isset($data['.']) && $data['.'] < 2)
                || (isset($data[' ']) && $data[' '] < 2)
                || (isset($data[',']) && $data[','] < 2)
            )
        ) {
            return 'Yes';
        }

        return 'No';
    }
}
