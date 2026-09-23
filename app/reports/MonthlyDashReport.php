<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class MonthlyDashReport
{
    private array $headers = [
        'ลำดับ',
        'ชื่อลูกค้า',
        'ผู้ดูแล',
        'สถานะรับเอกสาร',
        'สถานะทำบัญชี',
        'สถานะยื่นภาษี',
        'สถานะเก็บเงิน',
        'หมายเหตุ'
    ];

    public function exportMonthlyDash(array $data, string $monthName, string $fiscalYear, string $companyName, string $searchQuery = ''): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ลูกค้าทั้งหมด');
        
        // Filter data by search query
        $filteredData = [];
        foreach ($data as $t) {
            $match = true;
            if ($searchQuery !== '') {
                $q = mb_strtolower($searchQuery);
                $custName = mb_strtolower($t['customer_name'] ?? '');
                $caretaker = mb_strtolower($t['caretaker_firstname'] ?? '');
                if (mb_strpos($custName, $q) === false && mb_strpos($caretaker, $q) === false) {
                    $match = false;
                }
            }
            if ($match) {
                $filteredData[] = $t;
            }
        }
        
        // Draw Headers
        $sheet->setCellValue('A1', "รายงานภาพรวมงานรายเดือน - เดือน $monthName $fiscalYear (ลูกค้าทั้งหมด)");
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Add Header Row
        $col = 'A';
        foreach ($this->headers as $header) {
            $sheet->setCellValue($col . '3', $header);
            $col++;
        }
        
        // Style Header Row
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
            ],
        ];
        $sheet->getStyle('A3:H3')->applyFromArray($headerStyle);

        $rowNum = 4;
        $count = 1;

        if (empty($filteredData)) {
            $sheet->setCellValue('A4', 'ไม่พบข้อมูล');
            $sheet->mergeCells('A4:H4');
            $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        } else {
            foreach ($filteredData as $item) {
                $docReceived = (($item['doc_status'] ?? '0') === '1');
                $totalTasks = (int)($item['total_tasks'] ?? 0);
                $completedTasks = (int)($item['completed_tasks'] ?? 0);
                $isCompleted = ($totalTasks > 0 && $totalTasks === $completedTasks);
                $taxFiled = (($item['tax_status'] ?? '0') === '1');
                $paymentCollected = (($item['payment_status'] ?? '0') === '1');
                
                $docText = $docReceived ? 'ได้รับแล้ว' : 'ยังไม่ได้เอกสาร';
                $compText = $isCompleted ? 'เสร็จแล้ว' : 'ยังไม่เสร็จ (' . $completedTasks . '/' . $totalTasks . ')';
                $taxText = $taxFiled ? 'นำส่งแล้ว' : 'ยังไม่ยื่น';
                $payText = $paymentCollected ? 'เก็บเงินแล้ว' : 'ยังไม่เก็บ';

                $sheet->setCellValue('A' . $rowNum, $count);
                $sheet->setCellValue('B' . $rowNum, $item['customer_name'] ?? '');
                $sheet->setCellValue('C' . $rowNum, $item['caretaker_firstname'] ?? '-');
                $sheet->setCellValue('D' . $rowNum, $docText);
                $sheet->setCellValue('E' . $rowNum, $compText);
                $sheet->setCellValue('F' . $rowNum, $taxText);
                $sheet->setCellValue('G' . $rowNum, $payText);
                $sheet->setCellValue('H' . $rowNum, '');

                $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D' . $rowNum . ':G' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $rowNum++;
                $count++;
            }

            // Add Borders to Data Rows
            $sheet->getStyle('A4:H' . ($rowNum - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);
        }
        
        // Auto size columns
        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        // AutoFilter
        $sheet->setAutoFilter('A3:H3');

        // Header for download
        $filename = "Monthly_Dashboard_" . $monthName . "_" . $fiscalYear . ".xlsx";
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
