<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019-10-27
 * Time: 17:52
 */

namespace Enot\AdminBundle\Services;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelManager
{
    /** @var array */
    private $words = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L"];

    /**
     * @param array $headers
     * @param array $values
     * @param $name
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function createAndSave($headers, $values, $name)
    {
        $spreadsheet = $this->create($headers, $values);
        $this->save($spreadsheet, $name);

        return true;
    }


    /**
     * @param $headers
     * @param $values
     * @return Spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function create($headers, $values)
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);

        $this->prepareHeaders($spreadsheet, $headers);
        $this->prepareValues($spreadsheet, $values);

        return $spreadsheet;
    }

    /**
     * @param $spreadsheet
     * @param $name
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function save($spreadsheet, $name)
    {
        $writer = new Xlsx($spreadsheet);
        $writer->save("./reports/$name.xlsx");
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param array $headers
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function prepareHeaders(&$spreadsheet, $headers)
    {
        $worksheet = $spreadsheet->getActiveSheet();
        foreach ($headers as $key => $value) {
            $worksheet->setCellValue($this->words[$key] . "1", $value['title']);
            if (isset($value['options']['width'])) {
                $worksheet->getColumnDimension($this->words[$key], $value['options']['width']);
            }
            $worksheet->getCell($this->words[$key] . "1")->getStyle()->getFont()->setBold(true);
        }
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param $values
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function prepareValues(&$spreadsheet, $values)
    {
        foreach ($values as $key => $value) {
            $this->prepareValue($spreadsheet, $value, $key + 2);
        }
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param $row
     * @param $rowAt
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function prepareValue(&$spreadsheet, $row, $rowAt)
    {
        $worksheet = $spreadsheet->getActiveSheet();
        foreach ($row as $key => $column) {
            $worksheet->setCellValue($this->words[$key] . $rowAt, $column)
                ->getCell($this->words[$key] . $rowAt)->getStyle()
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }
    }
}