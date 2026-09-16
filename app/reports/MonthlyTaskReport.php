<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MonthlyTaskReport
{
    private array $headers = [
        'ลำดับ', 'เดือน', 'ลูกค้า', 'ผู้ทำบัญชี', 'ผู้สอบบัญชี', 'ผู้ดูแล',
        'ทีม', 'เอกสาร', 'งานประจำเดือน', 'รีวิว 1', 'รีวิว 2', 'รีวิว 3',
        'ยื่นภาษี', 'เก็บเงิน'
    ];

    public function exportMonthly(array $tasks, array $filters, int $month, string $fiscalYear, string $companyName): void
    {
        require_once __DIR__ . '/../models/monthly_task_Modal.php';
        $model = new \MonthlyTaskModal();

        $monthNames = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
            5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
            9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
        ];
        $monthName = $monthNames[$month] ?? '-';

        $spreadsheet = new Spreadsheet();
        
        if (empty($tasks)) {
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('ไม่มีข้อมูล');
            $sheet->setCellValue('A1', 'ไม่มีข้อมูลสำหรับเดือนนี้');
        } else {
            foreach ($tasks as $index => $task) {
                if ($index === 0) {
                    $sheet = $spreadsheet->getActiveSheet();
                } else {
                    $sheet = $spreadsheet->createSheet();
                }

                $customerName = $task['customer_name'] ?? 'ไม่ทราบชื่อ';
                // Excel sheet names max 31 chars and cannot contain certain characters
                $sheetTitle = mb_substr(str_replace(['*', ':', '/', '\\', '?', '[', ']'], '', $customerName), 0, 31);
                if (empty($sheetTitle)) $sheetTitle = 'Customer_' . ($index + 1);
                $sheet->setTitle($sheetTitle);
                $sheet->setShowGridLines(true);

                $title = "รายการงานรายเดือน {$monthName} ประจำปี {$fiscalYear} ของบริษัท {$customerName}";

                // Fetch detailed tasks
                $detailedTasks = $model->getTasksByPeriodId((int)$task['period_id']);

                // If no tasks, set a default column
                if (empty($detailedTasks)) {
                    $lastColumn = 'B';
                } else {
                    $lastColumn = $this->columnName(count($detailedTasks) + 1);
                }

                // Row 1: Header
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

                // Row 2: "รายการงาน"
                $sheet->mergeCells('A2:A3');
                $sheet->setCellValue('A2', '/');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                
                if (!empty($detailedTasks)) {
                    $sheet->mergeCells('B2:' . $lastColumn . '2');
                    $sheet->setCellValue('B2', 'รายการงาน');
                    $sheet->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                }

                // Row 3: Task Names (starts at B3)
                // Row 4: Status (starts at A4)
                // Row 5: Amount (starts at A5)
                $sheet->setCellValue('A4', 'สถานะงาน');
                $sheet->setCellValue('A5', 'จำนวนเงิน');

                $colIndex = 2; // Col B
                foreach ($detailedTasks as $dTask) {
                    $col = $this->columnName($colIndex);
                    
                    // Task Name
                    $sheet->setCellValue($col . '3', $dTask['task_name']);
                    
                    // Status
                    $statusText = ((string)$dTask['status'] === '1') ? 'เสร็จแล้ว' : 'รอดำเนินการ';
                    $sheet->setCellValue($col . '4', $statusText);

                    // Amount
                    $amount = (float)($dTask['amount'] ?? 0);
                    $sheet->setCellValue($col . '5', number_format($amount, 2));

                    $sheet->getColumnDimension($col)->setAutoSize(true);
                    $colIndex++;
                }

                // Styling
                $sheet->getColumnDimension('A')->setAutoSize(true);
                
                // Borders for the table A1 to LastCol5
                $sheet->getStyle('A1:' . $lastColumn . '5')->applyFromArray([
                    'borders' => ['allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
            }
        }

        $filename = 'monthly_task_' . $month . '_' . date('Ymd_His') . '.xlsx';
        
        if (ob_get_length()) {
            ob_end_clean();
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit();
    }

    public function exportCustomerYear(array $tasks, array $filters, int $customerId, string $customerName, string $fiscalYear, string $companyName): void
    {
        require_once __DIR__ . '/../models/monthly_task_Modal.php';
        $model = new \MonthlyTaskModal();

        $monthNames = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
            5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
            9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
        ];

        $tasksByMonth = [];
        foreach ($tasks as $task) {
            $tasksByMonth[(int) ($task['period_month'] ?? 0)] = $task;
        }

        $customerName = $customerName ?: ($tasks[0]['customer_name'] ?? '-');

        $activeMonths = [];
        $allTasks = []; // To store unique task names and their data
        
        // Loop 1 to 12 to maintain chronological order
        for ($month = 1; $month <= 12; $month++) {
            if (isset($tasksByMonth[$month])) {
                $periodId = (int)$tasksByMonth[$month]['period_id'];
                $detailedTasks = $model->getTasksByPeriodId($periodId);
                
                if (!empty($detailedTasks)) {
                    $activeMonths[] = $month;
                    foreach ($detailedTasks as $dTask) {
                        $tName = $dTask['task_name'];
                        if (!isset($allTasks[$tName])) {
                            $allTasks[$tName] = [];
                        }
                        $allTasks[$tName][$month] = [
                            'status' => $dTask['status'],
                            'amount' => $dTask['amount'] ?? 0,
                            'is_notify_amount' => $dTask['is_notify_amount'] ?? 1
                        ];
                    }
                }
            }
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheetTitle = mb_substr(str_replace(['*', ':', '/', '\\', '?', '[', ']'], '', $customerName), 0, 31);
        if (empty($sheetTitle)) $sheetTitle = 'Customer_' . $customerId;
        $sheet->setTitle($sheetTitle);
        $sheet->setShowGridLines(true);

        if (empty($activeMonths)) {
            $sheet->setCellValue('A1', 'ไม่มีข้อมูลงานในปีนี้');
        } else {
            $shortMonthNames = [
                1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
                5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
                9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
            ];

            // 1 column for task name, 2 columns per active month
            $totalCols = 1 + (count($activeMonths) * 2);
            $lastColumn = $this->columnName($totalCols);

            // Row 1: Header
            $title = "รายการงานรายเดือนของ {$customerName} ประจำปี {$fiscalYear} ของบริษัท {$companyName}";
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

            // Row 2: Headers (This row gets the AutoFilter)
            $sheet->setCellValue('A2', 'รายการงาน');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A2')->getFont()->setBold(true);

            $colIndex = 2; // Start at B
            foreach ($activeMonths as $month) {
                $monthAbbr = $shortMonthNames[$month] ?? '';
                $colStatus = $this->columnName($colIndex);
                $colAmount = $this->columnName($colIndex + 1);

                $sheet->setCellValue($colStatus . '2', $monthAbbr);
                $sheet->setCellValue($colAmount . '2', "จำนวนเงิน ({$monthAbbr})");
                
                $sheet->getStyle($colStatus . '2:' . $colAmount . '2')->getFont()->setBold(true);
                $sheet->getStyle($colStatus . '2:' . $colAmount . '2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

                $colIndex += 2;
            }

            // Apply AutoFilter to Row 2
            $sheet->setAutoFilter('A2:' . $lastColumn . '2');

            // Data Rows starting from row 3
            $currentRow = 3;
            foreach ($allTasks as $taskName => $monthData) {
                $sheet->setCellValue('A' . $currentRow, $taskName);
                
                $colIndex = 2;
                foreach ($activeMonths as $month) {
                    $colStatus = $this->columnName($colIndex);
                    $colAmount = $this->columnName($colIndex + 1);

                    if (isset($monthData[$month])) {
                        $statusText = ((string)$monthData[$month]['status'] === '1') ? 'เสร็จแล้ว' : 'รอดำเนินการ';
                        $amount = (float)$monthData[$month]['amount'];
                        
                        $sheet->setCellValue($colStatus . $currentRow, $statusText);
                        $sheet->setCellValue($colAmount . $currentRow, number_format($amount, 2));
                    } else {
                        $sheet->setCellValue($colStatus . $currentRow, '-');
                        $sheet->setCellValue($colAmount . $currentRow, '-');
                    }
                    $colIndex += 2;
                }
                $currentRow++;
            }

            // Styling Borders
            $sheet->getStyle('A1:' . $lastColumn . ($currentRow - 1))->applyFromArray([
                'borders' => ['allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ]],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ]);

            // Auto-size columns
            for ($i = 1; $i <= $totalCols; $i++) {
                $sheet->getColumnDimension($this->columnName($i))->setAutoSize(true);
            }
        }

        $filename = 'customer_monthly_task_' . $customerId . '_' . date('Ymd_His') . '.xlsx';
        
        if (ob_get_length()) {
            ob_end_clean();
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit();
    }

    private function export(array $tasks, string $sheetTitle, string $filename): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($sheetTitle, 0, 31));
        $sheet->setShowGridLines(true);

        $lastColumn = $this->columnName(count($this->headers));
        $sheet->mergeCells('A1:' . $lastColumn . '1');
        $sheet->setCellValue('A1', $sheetTitle);
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '107C41']],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(32);

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '107C41']],
            'borders' => ['allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'D0D7DE'],
            ]],
        ];

        foreach ($this->headers as $index => $header) {
            $column = $this->columnName($index + 1);
            $sheet->setCellValue($column . '2', $header);
        }
        $sheet->getStyle('A2:' . $lastColumn . '2')->applyFromArray($headerStyle);
        $sheet->getRowDimension(2)->setRowHeight(28);

        $monthNames = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
            5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
            9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
        ];

        $row = 3;
        foreach ($tasks as $index => $task) {
            $monthNumber = (int) ($task['period_month'] ?? 0);
            $total = (int) ($task['total_tasks'] ?? 0);
            $completed = (int) ($task['completed_tasks'] ?? 0);
            $review = static fn ($value) => ($value ?? '0') === '1' ? 'รีวิวแล้ว' : 'รอรีวิว';

            $values = [
                $index + 1,
                $monthNames[$monthNumber] ?? '-',
                $task['customer_name'] ?? '-',
                $task['rn_user'] ?? '-',
                $task['dbd_user'] ?? '-',
                $task['caretaker_firstname'] ?? '-',
                $task['team_name'] ?? '-',
                ($task['doc_status'] ?? '0') === '1' ? 'ได้รับเอกสาร' : 'ยังไม่ได้รับเอกสาร',
                $total > 0 && $total === $completed ? 'เสร็จสิ้น' : 'กำลังดำเนินงาน',
                $review($task['review1_status'] ?? '0'),
                $review($task['review2_status'] ?? '0'),
                $review($task['review3_status'] ?? '0'),
                ($task['tax_status'] ?? '0') === '1' ? 'ยื่นแล้ว' : 'ยังไม่ได้ยื่น',
                ($task['payment_status'] ?? '0') === '1' ? 'ได้รับเงินแล้ว' : 'ยังไม่ได้รับเงิน',
            ];

            foreach ($values as $valueIndex => $value) {
                $sheet->setCellValue($this->columnName($valueIndex + 1) . $row, $value);
            }
            $row++;
        }

        $sheet->getStyle('A2:' . $lastColumn . max(2, $row - 1))
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setRGB('D0D7DE');
        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        if (ob_get_length()) {
            ob_end_clean();
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit();
    }

    private function columnName(int $number): string
    {
        $column = '';
        while ($number > 0) {
            $remainder = ($number - 1) % 26;
            $column = chr(65 + $remainder) . $column;
            $number = intdiv($number - 1, 26);
        }
        return $column;
    }
}