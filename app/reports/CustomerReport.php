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
        // แก้จาก A1:J1 -> A1:P1 ให้ครอบคลุมทุกคอลัมน์ที่มี header จริง
        $sheet->getStyle('A1:P1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // 3. นำข้อมูล $customers ที่ได้รับมาใส่ลงในแต่ละแถว
        $row = 2;
        $i = 1;
        foreach ($customers as $c) {
            $statusText     = ($c['active_status'] == 1) ? 'ใช้บริการอยู่' : 'เลิกจ้าง';
            $caretaker      = trim(($c['caretaker_firstname'] ?? '') . ' ' . ($c['caretaker_lastname'] ?? ''));
            $closingDate    = !empty($c['fiscal_closing_date']) ? date('d/m/Y', strtotime($c['fiscal_closing_date'])) : '-';
            $accountsAmount = floatval($c['accounts_amount'] ?? 0);

            // --- แก้ไขตำแหน่งคอลัมน์ให้ตรงกับ Header ---
            $sheet->setCellValue('A' . $row, $i++);
            $sheet->setCellValue('B' . $row, $caretaker ?: '-');            // ผู้ดูแล
            $sheet->setCellValue('C' . $row, $c['customer_name'] ?? '');    // ชื่อลูกค้า
            $sheet->setCellValue('D' . $row, $statusText);                  // สถานะ
            $sheet->setCellValue('E' . $row, $c['team_name'] ?: '-');       // ทีม
            $sheet->setCellValue('F' . $row, $closingDate);                 // วันสิ้นรอบบัญชี
            $sheet->setCellValue('G' . $row, $accountsAmount);              // ค่าบริการรายเดือน
            $sheet->setCellValue('H' . $row, $c['customer_phone'] ?: '-');  // เบอร์โทร
            $sheet->setCellValue('I' . $row, $c['customer_email'] ?: '-');  // อีเมล
            $sheet->setCellValue('J' . $row, $c['line_id'] ?: '-');         // Line ID

            // --- คอลัมน์ K-P: ข้อมูลราชการ ---
            // K, M, O: ยังไม่มีคอลัมน์นี้ในฐานข้อมูล (เลขผู้เสียภาษี / เลข DBD / เลขประกันสังคม)
            // ถ้าต้องการเก็บจริง ต้อง ALTER TABLE เพิ่มคอลัมน์ก่อน เช่น tax_id, dbd_reg_no, sso_no
            $sheet->setCellValue('K' . $row, $c['rn_user'] ?? '-');          // placeholder รอเพิ่มคอลัมน์จริง
            $sheet->setCellValue('L' . $row, $c['rn_password'] ?: '-');         // รหัสกรมสรรพากร (มีจริงใน DB)
            $sheet->setCellValue('M' . $row, $c['dbd_user'] ?? '-');      // placeholder รอเพิ่มคอลัมน์จริง
            $sheet->setCellValue('N' . $row, $c['dbd_password'] ?: '-');       // รหัสกรมพัฒนาธุรกิจการค้า (มีจริงใน DB)
            $sheet->setCellValue('O' . $row, $c['sso_user'] ?? '-');          // placeholder รอเพิ่มคอลัมน์จริง
            $sheet->setCellValue('P' . $row, $c['sso_password'] ?: '-');       // รหัสประกันสังคม (มีจริงใน DB)

            // จัดตำแหน่งและการแสดงผลของแต่ละเซลล์
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('K' . $row . ':P' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

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
            // แก้จาก A:J -> A:P ให้ครอบคลุมแถวสรุปทั้งหมด
            $sheet->getStyle('A' . $row . ':P' . $row)->applyFromArray($totalRowStyle);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getRowDimension($row)->setRowHeight(24);

            // ใส่เส้นขอบทุกช่อง (แก้ A1:J -> A1:P)
            $sheet->getStyle('A1:P' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D0D7DE');
        } else {
            $sheet->getStyle('A1:P1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D0D7DE');
        }

        // 5. ปรับขนาดความกว้างคอลัมน์อัตโนมัติ (แก้ A-J -> A-P)
        foreach (range('A', 'P') as $col) {
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