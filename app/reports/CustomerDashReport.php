<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CustomerDashReport
{
    public function export(array $customers, string $fiscalYear, string $companyName, string $monthName = ''): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('แดชบอร์ดลูกค้า');
        $headers = ['ลำดับ', 'ลูกค้า', 'ผู้ดูแล', 'ทีม', 'เดือนที่มีงาน', 'งานทั้งหมด', 'งานเสร็จแล้ว', 'ยอดทำบัญชี / เดือน', 'ค่าปิดบัญชี', 'ค่าสอบบัญชี', 'สถานะ'];
        $lastColumn = $this->columnName(count($headers));
        
        $title = 'ภาพรวมงานของลูกค้า ประจำปี ' . $fiscalYear . ' ของบริษัท ' . $companyName;
        if ($monthName !== '') {
            $title .= ' (เดือน ' . $monthName . ')';
        }

        $sheet->mergeCells('A1:' . $lastColumn . '1');
        $sheet->setCellValue('A1', $title);
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        foreach ($headers as $index => $header) {
            $sheet->setCellValue($this->columnName($index + 1) . '2', $header);
        }
        $sheet->getStyle('A2:' . $lastColumn . '2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->setAutoFilter('A2:' . $lastColumn . '2');

        $row = 3;
        foreach ($customers as $index => $customer) {
            $totalTasks = (int) ($customer['total_tasks'] ?? 0);
            $completedTasks = (int) ($customer['completed_tasks'] ?? 0);
            $values = [
                $index + 1,
                $customer['customer_name'] ?? '-',
                trim(($customer['caretaker_firstname'] ?? '') . ' ' . ($customer['caretaker_lastname'] ?? '')) ?: '-',
                $customer['team_name'] ?? '-',
                (int) ($customer['work_months'] ?? 0),
                $totalTasks,
                $completedTasks,
                (float) ($customer['accounts_amount'] ?? 0),
                is_null($customer['closing_amount']) ? 'NULL' : (float) $customer['closing_amount'],
                is_null($customer['auditing_amount']) ? 'NULL' : (float) $customer['auditing_amount'],
                $totalTasks > 0 && $totalTasks === $completedTasks ? 'เสร็จครบแล้ว' : 'ยังดำเนินงาน',
            ];
            foreach ($values as $valueIndex => $value) {
                if ($value === 0 || $value === 0.0 || $value === '0') {
                    $value = '-';
                }
                $sheet->setCellValue($this->columnName($valueIndex + 1) . $row, $value);
            }
            $row++;
        }

        $sheet->getStyle('A1:' . $lastColumn . max(2, $row - 1))
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setRGB('000000');
        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        if (ob_get_length()) {
            ob_end_clean();
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="customer_dashboard_' . date('Ymd_His') . '.xlsx"');
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
