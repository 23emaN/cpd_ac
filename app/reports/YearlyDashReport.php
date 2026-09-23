<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class YearlyDashReport
{
    public function exportYearlyDash(array $closingList, string $fiscalYear, string $companyName, bool $isSuperAdmin = false): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ภาพรวมงานรายปี');

        // Header Title
        $sheet->setCellValue('A1', "รายงานภาพรวมงานรายปี - ปีบัญชี $fiscalYear");
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Table Headers
        $headers = [
            'A' => 'รายชื่อลูกค้า',
            'B' => 'ปิดงบเสร็จแล้ว',
            'C' => 'ได้รับงบ/คืนลูกค้าแล้ว',
            'D' => 'บอจ. 5',
            'E' => 'DBD E-Filing',
            'F' => 'ภ.ง.ด.50',
            'G' => 'ผู้ดูแล'
        ];

        foreach ($headers as $col => $text) {
            $sheet->setCellValue($col . '3', $text);
        }

        // Style Header Row (No background color as requested)
        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ]
        ];
        $sheet->getStyle('A3:G3')->applyFromArray($headerStyle);

        // Sort data by Customer Name
        usort($closingList, function ($a, $b) {
            return strcmp($a['customer_name'] ?? '', $b['customer_name'] ?? '');
        });

        // Fill Data
        $rowNum = 4;
        foreach ($closingList as $item) {
            $caretakerName = trim(($item['user_firstname'] ?? '') . ' ' . ($item['user_lastname'] ?? ''));
            if ($caretakerName === '') $caretakerName = 'ไม่ระบุผู้ดูแล';

            $isClosingDone = ((string)($item['closing_status'] ?? '')) === '1';
            $isAuditDone = ((string)($item['audit_status'] ?? '')) === '1' || ((string)($item['doc_status'] ?? '')) === '1';
            $isBoj5Done = ((string)($item['boj5_status'] ?? '')) === '1';
            $isDbdDone = ((string)($item['dbd_efiling_status'] ?? '')) === '1';
            $isPnd50Done = ((string)($item['pnd50_status'] ?? '')) === '1';

            $sheet->setCellValue('A' . $rowNum, $item['customer_name'] ?? '');
            $sheet->setCellValue('B' . $rowNum, $isClosingDone ? 'เสร็จแล้ว' : 'ยังไม่เสร็จ');
            $sheet->setCellValue('C' . $rowNum, $isAuditDone ? 'เสร็จแล้ว' : 'ยังไม่เสร็จ');
            $sheet->setCellValue('D' . $rowNum, $isBoj5Done ? 'เสร็จแล้ว' : 'ยังไม่เสร็จ');
            $sheet->setCellValue('E' . $rowNum, $isDbdDone ? 'เสร็จแล้ว' : 'ยังไม่เสร็จ');
            $sheet->setCellValue('F' . $rowNum, $isPnd50Done ? 'เสร็จแล้ว' : 'ยังไม่เสร็จ');
            $sheet->setCellValue('G' . $rowNum, $caretakerName);

            $rowNum++;
        }

        // Apply styles to data rows
        if ($rowNum > 4) {
            $sheet->getStyle('A4:G' . ($rowNum - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ]);
            $sheet->getStyle('C4:G' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Auto size columns
        foreach (range('A', 'G') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Add AutoFilter
        $sheet->setAutoFilter('A3:G' . ($rowNum - 1));

        // Output File
        $filename = "Yearly_Dashboard_" . $fiscalYear . ".xlsx";
        if (ob_get_length() > 0) { ob_clean(); }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
