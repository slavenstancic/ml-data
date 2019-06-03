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
    private $dir = __DIR__ . '/../documents/';

    private $characters = ['/', ':', ',', '.', ' '];

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
    public function countAllFilesData()
    {
        $spreadsheet = new Spreadsheet();
        $alphabet = range('A', 'Z');
        $data = $this->iterateFiles();
        $sheetIndex = 0;

        foreach ($data as $file => $row) {
            $newSheet = new Worksheet($spreadsheet, $file);
            $spreadsheet->addSheet($newSheet, $sheetIndex);
            $spreadsheet->setActiveSheetIndex($sheetIndex);
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);
            $spreadsheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(100);

            foreach ($row as $rowNum => $column) {
                foreach ($column as $columnNum => $data) {
                    $column = sprintf('%s%d', $alphabet[$columnNum], $rowNum);

                    $output = implode("\n", array_map(
                        function ($v, $k) {
                            return sprintf("%s%s = %s ", strlen($k) === 1 ? '# of ' : '', $k, $v);
                        },
                        $data,
                        array_keys($data)
                    ));

                    $spreadsheet->getActiveSheet()->setCellValue($column, $output);
                }
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
    private function iterateFiles()
    {
        $files = array_diff(scandir($this->dir), ['..', '.']);
        $return = [];
        foreach ($files as $file) {
            $filePath = $this->dir . $file;
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
            foreach ($cells as $cell => $value) {
                foreach ($this->characters as $character) {
                    $result[$row][$cell][$character] = substr_count($value, $character);
                }
                $result[$row][$cell]['# of digits'] = preg_match_all("/[0-9]/", $value);
                $result[$row][$cell]['# of characters'] = strlen($value);

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
        if ($data['digits'] >= 4
            && $data['characters'] === 0
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
        if ($data['digits'] >= 4
            && $data['characters'] === 0
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
        if ($data['digits'] > 0
            && $data['characters'] === 0
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
