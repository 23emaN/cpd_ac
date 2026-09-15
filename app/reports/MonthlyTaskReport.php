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
        $monthNames = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
            5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
            9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
        ];
        $title = 'รายงานจัดการงานรายเดือน ' . ($monthNames[$month] ?? '-')
            . ' ประจำปี ' . $fiscalYear . ' ของบริษัท ' . $companyName;
        $this->export($tasks, $title, 'monthly_task_' . $month . '_' . date('Ymd_His') . '.xlsx');
    }

    public function exportCustomerYear(array $tasks, array $filters, int $customerId, string $customerName, string $fiscalYear, string $companyName): void
    {
        $tasksByMonth = [];
        foreach ($tasks as $task) {
            $tasksByMonth[(int) ($task['period_month'] ?? 0)] = $task;
        }

        $customerName = $customerName ?: ($tasks[0]['customer_name'] ?? '-');
        $annualTasks = [];
        for ($month = 1; $month <= 12; $month++) {
            $annualTasks[] = $tasksByMonth[$month] ?? [
                'period_month' => $month,
                'customer_name' => $customerName,
                'doc_status' => '0',
                'tax_status' => '0',
                'payment_status' => '0',
                'review1_status' => '0',
                'review2_status' => '0',
                'review3_status' => '0',
                'total_tasks' => 0,
                'completed_tasks' => 0,
            ];
        }

        $title = 'รายงานจัดการงานรายเดือนของ ' . $customerName
            . ' ประจำปี ' . $fiscalYear . ' ของบริษัท ' . $companyName;
        $this->export($annualTasks, $title, 'customer_monthly_task_' . $customerId . '_' . date('Ymd_His') . '.xlsx');
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