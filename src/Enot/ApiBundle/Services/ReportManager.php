<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019-09-11
 * Time: 13:02
 */

namespace Enot\ApiBundle\Services;


use Enot\ApiBundle\Entity\Transportation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportManager
{
    private $letters = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K"];

    /**
     * @param $transportations
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function export($transportations)
    {
        $spreadSheet = new Spreadsheet();

        $spreadSheet->setActiveSheetIndex(0);

        $sheet = $spreadSheet->getActiveSheet();
        $this->setHeader($sheet, [
            "Партнер", "ФИО", "Откуда", "Куда", "Номер машины", "Номер груза", "Тип перевозки", "Стоимость", "Примерная стоимость", "Документы"
        ]);

        /** @var Transportation[] $transportations */
        foreach ($transportations as $position => $transportation) {
            $this->setValue($sheet, $transportation, $position + 2);
        }


// Redirect output to a client’s web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="01simple.xlsx"');
        header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($spreadSheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    public function setHeader(Worksheet &$sheet, array $headers)
    {
        foreach ($headers as $position => $header) {
            $sheet->setCellValue($this->letters[$position] . "1", $header);
        }
    }

    public function setValue(Worksheet &$sheet, Transportation $transportation, $position)
    {
        $sheet->setCellValue($this->letters[0] . $position, $transportation->getVehicle()->getPartner()->getName());
        $sheet->setCellValue($this->letters[1] . $position, $transportation->getDriver()->getName());
        $sheet->setCellValue($this->letters[2] . $position, $transportation->getDriver()->getName());
        $sheet->setCellValue($this->letters[3] . $position, $transportation->getDriver()->getName());
        $sheet->setCellValue($this->letters[4] . $position, $transportation->getDriver()->getName());
        $sheet->setCellValue($this->letters[5] . $position, $transportation->getDriver()->getName());
        $sheet->setCellValue($this->letters[6] . $position, $transportation->getDriver()->getName());
        $sheet->setCellValue($this->letters[7] . $position, $transportation->getDriver()->getName());
        $sheet->setCellValue($this->letters[8] . $position, $transportation->getDriver()->getName());
        $sheet->setCellValue($this->letters[9] . $position, $transportation->getDriver()->getName());
        $sheet->setCellValue($this->letters[10] . $position, $transportation->getDriver()->getName());
    }
}