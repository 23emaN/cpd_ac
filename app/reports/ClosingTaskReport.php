<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ClosingTaskReport
{
    private array $headers = [
        'ลำดับ',
        'ลูกค้า',
        'รอบบัญชี',
        'ผู้ใช้ (RD)',
        'รหัสผ่าน (RD)',
        'รหัสผ่าน (DBD)',
        'ผู้ดูแล',
        'สถานะปิดงบ',
        'สถานะผู้สอบ',
        'บอจ. 5',
        'DBD E-Filing',
        'ภ.ง.ด.50'
    ];

    public function exportClosing(array $data, string $fiscalYear, string $companyName): void
    {
        $spreadsheet = new Spreadsheet();
        
        if (empty($data)) {
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('ไม่มีข้อมูล');
            $sheet->setCellValue('A1', 'ไม่มีข้อมูลลูกค้าในปีบัญชีนี้');
        } else {
            foreach ($data as $index => $row) {
                if ($index === 0) {
                    $sheet = $spreadsheet->getActiveSheet();
                } else {
                    $sheet = $spreadsheet->createSheet();
                }
                
                $customerName = trim($row['customer_name'] ?? '');
                // Invalid characters in sheet names: \ / ? * : [ ]
                $safeName = str_replace(['\\', '/', '?', '*', ':', '[', ']'], '', $customerName);
                if (empty($safeName)) {
                    $safeName = 'Customer_' . ($index + 1);
                }
                $sheetTitle = mb_substr($safeName, 0, 31);
                $sheet->setTitle($sheetTitle);
                $sheet->setShowGridLines(true);

                $title = "รายงานสถานะปิดงบประจำปีบัญชี {$fiscalYear} ของบริษัท {$companyName}";

                // Row 1: Header
                $lastColumn = 'L'; // 12 columns (A to L)
                $sheet->mergeCells('A1:' . $lastColumn . '1');
                $sheet->setCellValue('A1', $title);
                $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);

                // Row 2: Headers (No background color)
                $colIndex = 'A';
                foreach ($this->headers as $header) {
                    $sheet->setCellValue($colIndex . '2', $header);
                    $colIndex++;
                }
                $sheet->getStyle('A2:' . $lastColumn . '2')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ]
                    ]
                ]);
                // Add Excel AutoFilter to the headers
                $sheet->setAutoFilter('A2:' . $lastColumn . '2');

                // Row 3: Data
                // Status Calculations
                $docStatus    = ($row['doc_status']    ?? '0') === '1';
                $docDate      = !empty($row['doc_date']);
                $closingStatus= ($row['closing_status'] ?? '0') === '1';
                $closingDate  = !empty($row['closing_date']);
                
                $closingLabel = 'รอเอกสาร';
                if ($docStatus && $docDate && $closingStatus && $closingDate) {
                    $closingLabel = 'ปิดงบแล้ว';
                } elseif ($docStatus && $docDate) {
                    $closingLabel = 'ได้รับเอกสารแล้ว';
                }

                $auditStatus      = ($row['audit_status'] ?? '0') === '1';
                $auditDate        = !empty($row['audit_date']);
                $budgetRefundDate = !empty($row['budget_refund_date']);
                
                $auditLabel = 'ยังไม่ได้ตรวจ';
                if ($auditStatus && $auditDate && $budgetRefundDate) {
                    $auditLabel = 'ได้รับงานคืนแล้ว';
                } elseif ($auditStatus && $auditDate) {
                    $auditLabel = 'ตรวจแล้ว';
                }

                $boj5Status = ($row['boj5_status'] ?? '0') === '1';
                $boj5Label  = ($boj5Status && !empty($row['boj5_date'])) ? 'นำส่งแล้ว' : 'ยังไม่ได้ยื่น';

                $dbdStatus = ($row['dbd_efiling_status'] ?? '0') === '1';
                $dbdLabel  = ($dbdStatus && !empty($row['dbd_efiling_date'])) ? 'นำส่งแล้ว' : 'ยังไม่ได้ยื่น';

                $pnd50Status = (string)($row['pnd50_status'] ?? '0');
                if ($pnd50Status === '1' && !empty($row['pnd50_date'])) {
                    $pnd50Label = 'นำส่งแล้ว';
                } elseif ($pnd50Status === '2') {
                    $pnd50Label = 'รอเอกสาร';
                } else {
                    $pnd50Label = 'ยังไม่ได้ยื่น';
                }

                $fiscalClosingDate = !empty($row['fiscal_closing_date']) ? date('d/m/Y', strtotime($row['fiscal_closing_date'])) : '-';
                $caretakerName = trim(($row['user_firstname'] ?? '') . ' ' . ($row['user_lastname'] ?? ''));
                if (empty($caretakerName)) $caretakerName = '-';

                // We reset the index to 1 for this customer's data row, or use the overall index? 
                // Since it's one row per sheet, index is just 1.
                $rowIndex = 3;
                $sheet->setCellValue('A' . $rowIndex, 1);
                $sheet->setCellValue('B' . $rowIndex, $customerName);
                $sheet->setCellValue('C' . $rowIndex, $fiscalClosingDate);
                $sheet->setCellValue('D' . $rowIndex, $row['rd_user_name'] ?? '-');
                $sheet->setCellValue('E' . $rowIndex, $row['rd_password'] ?? '-');
                $sheet->setCellValue('F' . $rowIndex, $row['dbd_password'] ?? '-');
                $sheet->setCellValue('G' . $rowIndex, $caretakerName);
                $sheet->setCellValue('H' . $rowIndex, $closingLabel);
                $sheet->setCellValue('I' . $rowIndex, $auditLabel);
                $sheet->setCellValue('J' . $rowIndex, $boj5Label);
                $sheet->setCellValue('K' . $rowIndex, $dbdLabel);
                $sheet->setCellValue('L' . $rowIndex, $pnd50Label);

                $sheet->getStyle('A' . $rowIndex . ':L' . $rowIndex)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ]
                    ]
                ]);
                
                $sheet->getStyle('A' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('G' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('H' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('I' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('J' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('K' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('L' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Auto size columns
                $cols = ['A','B','C','D','E','F','G','H','I','J','K','L'];
                foreach ($cols as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            }
            // Set active sheet to the first one before saving
            $spreadsheet->setActiveSheetIndex(0);
        }

        // Output to browser
        $fileName = 'รายงานสถานะปิดงบ_ปีบัญชี_' . $fiscalYear . '.xlsx';
        
        if (ob_get_length()) ob_end_clean(); // Ensure no previous output
        if (ob_get_length() > 0) { ob_clean(); }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
