<?php

// ตรวจสอบและโหลด Composer Autoload อัตโนมัติหากยังไม่ถูกโหลด
if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
    $autoloadPaths = [
        __DIR__ . '/../../vendor/autoload.php',
        __DIR__ . '/../vendor/autoload.php',
        __DIR__ . '/vendor/autoload.php',
        $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php',
        $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php',
    ];
    foreach ($autoloadPaths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
}

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

        // 2. กำหนดชื่อคอลัมน์ (A ถึง P รวม 16 คอลัมน์)
        $headers = [
            'A1' => 'ลำดับ',
            'B1' => 'ผู้ดูแล',
            'C1' => 'ชื่อลูกค้า',
            'D1' => 'สถานะ',
            'E1' => 'ทีม',
            'F1' => 'วันสิ้นรอบบัญชี',
            'G1' => 'ค่าบริการรายเดือน (บาท)',
            'H1' => 'เบอร์โทรติดต่อ',
            'I1' => 'อีเมล',
            'J1' => 'Line ID',
            'K1' => 'เลขประจำตัวผู้เสียภาษี',
            'L1' => 'รหัสกรมสรรพากร',
            'M1' => 'เลขกรมพัฒนาธุรกิจการค้า',
            'N1' => 'รหัสกรมพัฒนาธุรกิจการค้า',
            'O1' => 'เลขประกันสังคม',
            'P1' => 'รหัสประกันสังคม',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }
        $sheet->getStyle('A1:P1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // 3. นำข้อมูล $customers ที่ได้รับมาใส่ลงในแต่ละแถว
        $row = 2;
        $i = 1;
        foreach ($customers as $c) {
            $statusText     = (isset($c['active_status']) && $c['active_status'] == 1) ? 'ใช้บริการอยู่' : 'เลิกจ้าง';
            $caretaker       = trim(($c['caretaker_firstname'] ?? '') . ' ' . ($c['caretaker_lastname'] ?? ''));
            $closingDate     = !empty($c['fiscal_closing_date']) ? date('d/m/Y', strtotime($c['fiscal_closing_date'])) : '-';
            $accountsAmount = floatval($c['accounts_amount'] ?? 0);

            // --- วางข้อมูลลงในคอลัมน์ A ถึง P ---
            $sheet->setCellValue('A' . $row, $i++);
            $sheet->setCellValue('B' . $row, $caretaker ?: '-');
            $sheet->setCellValue('C' . $row, $c['customer_name'] ?? '');
            $sheet->setCellValue('D' . $row, $statusText);
            $sheet->setCellValue('E' . $row, !empty($c['team_name']) ? $c['team_name'] : '-');
            $sheet->setCellValue('F' . $row, $closingDate);
            $sheet->setCellValue('G' . $row, $accountsAmount);
            $sheet->setCellValueExplicit('H' . $row, (string)($c['customer_phone'] ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('I' . $row, !empty($c['customer_email']) ? $c['customer_email'] : '-');
            $sheet->setCellValue('J' . $row, !empty($c['line_id']) ? $c['line_id'] : '-');

            // ข้อมูลราชการ (ใช้ setCellValueExplicit เพื่อป้องกันเลข 0 นำหน้าหาย)
            $sheet->setCellValueExplicit('K' . $row, (string)($c['rn_user'] ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('L' . $row, (string)($c['rn_password'] ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('M' . $row, (string)($c['dbd_user'] ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('N' . $row, (string)($c['dbd_password'] ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('O' . $row, (string)($c['sso_user'] ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('P' . $row, (string)($c['sso_password'] ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            // จัดตำแหน่งและการแสดงผลของแต่ละเซลล์
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('K' . $row . ':P' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        $lastDataRow = $row - 1;

        // 4. แถวสรุปผลรวมท้ายตาราง
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
            $sheet->getStyle('A' . $row . ':P' . $row)->applyFromArray($totalRowStyle);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getRowDimension($row)->setRowHeight(24);

            $sheet->getStyle('A1:P' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D0D7DE');
        } else {
            $sheet->getStyle('A1:P1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D0D7DE');
        }

        // 5. ปรับขนาดความกว้างคอลัมน์อัตโนมัติ
        foreach (range('A', 'P') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 6. ล้าง Output Buffer ทุกระดับ เพื่อป้องกันไฟล์เสีย
        if (ob_get_length() > 0) { ob_clean(); }

        // 7. จัดการชื่อไฟล์ภาษาไทยให้ถูกต้องตามมาตรฐาน RFC 5987
        $filename = $options['filename'] ?? ('customer_list_' . date('Ymd_His') . '.xlsx');
        if (!preg_match('/\.xlsx$/i', $filename)) {
            $filename .= '.xlsx';
        }
        
        $fallbackFilename = preg_replace('/[^\w\-\.]/', '_', $filename);
        $encodedFilename = rawurlencode($filename);

        // Header ส่งไฟล์ดาวน์โหลด
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fallbackFilename . '"; filename*=UTF-8\'\'' . $encodedFilename);
        header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate');
        header('Pragma: public');
        header('Expires: 0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }
}