<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CustomerReport
{
    /**
     * รับข้อมูล data จาก Controller แล้วนำมาสร้างไฟล์ Excel ส่งออก
     *
     * @param array $customers ข้อมูลรายชื่อลูกค้าที่คิวรี่มาจาก Model
     * @param array $options การตั้งค่าเพิ่มเติม เช่น ชื่อไฟล์ หรือหัวรายงาน
     */
    public function export(array $customers, array $options = [])
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('รายชื่อลูกค้า');
        $sheet->setShowGridLines(true);

        // 1. กำหนดรูปแบบสไตล์ของหัวตาราง (Header Style)
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '107C41'], // สีเขียว Excel
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        // 2. กำหนดชื่อคอลัมน์
        $headers = [
            'A1' => 'ลำดับ',
            'B1' => 'ชื่อลูกค้า',
            'C1' => 'สถานะ',
            'D1' => 'ทีม',
            'E1' => 'ผู้ดูแล',
            'F1' => 'วันสิ้นรอบบัญชี',
            'G1' => 'ค่าบัญชี (บาท/เดือน)',
            'H1' => 'เบอร์โทรติดต่อ',
            'I1' => 'อีเมล',
            'J1' => 'Line ID',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // 3. นำข้อมูล $customers ที่ได้รับมาใส่ลงในแต่ละแถว
        $row = 2;
        $i = 1;
        foreach ($customers as $c) {
            $statusText = ($c['active_status'] == 1) ? 'ใช้บริการอยู่' : 'เลิกจ้าง';
            $caretaker = trim(($c['caretaker_firstname'] ?? '') . ' ' . ($c['caretaker_lastname'] ?? ''));
            $closingDate = !empty($c['fiscal_closing_date']) ? date('d/m/Y', strtotime($c['fiscal_closing_date'])) : '-';
            $accountsAmount = floatval($c['accounts_amount'] ?? 0);

            $sheet->setCellValue('A' . $row, $i++);
            $sheet->setCellValue('B' . $row, $c['customer_name'] ?? '');
            $sheet->setCellValue('C' . $row, $statusText);
            $sheet->setCellValue('D' . $row, $c['team_name'] ?: '-');
            $sheet->setCellValue('E' . $row, $caretaker ?: '-');
            $sheet->setCellValue('F' . $row, $closingDate);
            $sheet->setCellValue('G' . $row, $accountsAmount);
            $sheet->setCellValue('H' . $row, $c['customer_phone'] ?: '-');
            $sheet->setCellValue('I' . $row, $c['customer_email'] ?: '-');
            $sheet->setCellValue('J' . $row, $c['line_id'] ?: '-');

            // จัดตำแหน่งและการแสดงผลของแต่ละเซลล์
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        $lastDataRow = $row - 1;

        // 4. แถวสรุปผลรวมท้ายตาราง (ถ้ามีข้อมูล)
        if ($lastDataRow >= 2) {
            $sheet->mergeCells('A' . $row . ':F' . $row);
            $sheet->setCellValue('A' . $row, 'รวมค่าบัญชีทั้งสิ้น');
            $sheet->setCellValue('G' . $row, '=SUM(G2:G' . $lastDataRow . ')');

            $totalRowStyle = [
                'font' => ['bold' => true],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8F5E9'],
                ],
            ];
            $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray($totalRowStyle);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getRowDimension($row)->setRowHeight(24);

            // ใส่เส้นขอบทุกช่อง
            $sheet->getStyle('A1:J' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D0D7DE');
        } else {
            $sheet->getStyle('A1:J1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D0D7DE');
        }

        // 5. ปรับขนาดความกว้างคอลัมน์อัตโนมัติ
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 6. เคลียร์ Output Buffer ก่อนดาวน์โหลด ป้องกันไฟล์เสียหาย
        if (ob_get_length()) {
            ob_end_clean();
        }

        $filename = $options['filename'] ?? ('customer_list_' . date('Ymd_His') . '.xlsx');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }
}
