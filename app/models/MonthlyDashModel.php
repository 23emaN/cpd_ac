<?php
require_once '../app/models/Model.php';

class MonthlyDashModel extends Model {

    /**
     * ดึงข้อมูลงานรายเดือนของแต่ละช่วงเวลาทำงาน (Work Periods)
     */
    /**
     * ดึงข้อมูลงานรายเดือนของแต่ละช่วงเวลาทำงาน (Work Periods)
     */
    public function getMonthlyTasks($fiscalId, $month = null) {
        $params = ['fiscal_id' => $fiscalId];

        $where = "wp.fiscal_year_id = :fiscal_id AND c.delete_at IS NULL";

        if ($month !== null && $month !== '') {
            // บังคับเทียบทั้งแบบมี 0 นำหน้า ('02') และไม่มี 0 ('2')
            $where .= " AND (wp.period_month = :month_pad OR wp.period_month = :month_raw)";
            $params['month_pad'] = str_pad((int)$month, 2, '0', STR_PAD_LEFT); // เช่น '02'
            $params['month_raw'] = (string)(int)$month;                         // เช่น '2'
        }

        $sql = "
            SELECT 
                wp.period_id,
                wp.period_month,
                wp.doc_status,
                wp.tax_status,
                wp.payment_status,
                wp.review1_status,
                wp.review2_status,
                wp.review3_status,
                c.customer_id,
                c.customer_name,
                c.rn_user,
                c.dbd_user,
                c.sso_user,
                fyc.accounts_amount,
                t.team_name,
                u.user_firstname AS caretaker_firstname,
                (SELECT COUNT(*) FROM tbl_customer_tasks WHERE period_id = wp.period_id) AS total_tasks,
                (SELECT COUNT(*) FROM tbl_customer_tasks WHERE period_id = wp.period_id AND status = '1') AS completed_tasks
            FROM tbl_customer_work_periods wp
            LEFT JOIN tbl_customers c 
                ON wp.customer_id = c.customer_id
            LEFT JOIN tbl_fiscal_year_customers fyc 
                ON wp.customer_id = fyc.customer_id AND wp.fiscal_year_id = fyc.fiscal_id
            LEFT JOIN tbl_user u 
                ON fyc.user_id = u.user_id
            LEFT JOIN tbl_team t 
                ON fyc.team_id = t.team_id
            WHERE {$where}
            ORDER BY c.created_at ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * สรุปข้อมูลสถิติภาพรวมสำหรับแสดงผลบน Dashboard
     */
    public function getDashboardStats($fiscalId, $month = null) {
        $tasks = $this->getMonthlyTasks($fiscalId, $month);
        
        $totalCustomers = count($tasks);
        $docReceived = 0;
        $completed = 0;
        $reviewed = 0;
        $taxFiled = 0;
        $paymentCollected = 0;

        $caretakersMap = [];

        foreach ($tasks as $t) {
            if (($t['doc_status'] ?? '0') === '1') {
                $docReceived++;
            }
            
            $totalTasks = (int)($t['total_tasks'] ?? 0);
            $completedTasks = (int)($t['completed_tasks'] ?? 0);
            $isDone = ($totalTasks > 0 && $totalTasks === $completedTasks);
            if ($isDone) {
                $completed++;
            }

            if (($t['review1_status'] ?? '0') === '1') {
                $reviewed++;
            }

            if (($t['tax_status'] ?? '0') === '1') {
                $taxFiled++;
            }

            if (($t['payment_status'] ?? '0') === '1') {
                $paymentCollected++;
            }

            // Caretaker grouping
            $caretakerName = !empty($t['caretaker_firstname']) ? $t['caretaker_firstname'] : 'ไม่ระบุผู้ดูแล';
            if (!isset($caretakersMap[$caretakerName])) {
                $caretakersMap[$caretakerName] = [
                    'name' => $caretakerName,
                    'total' => 0,
                    'completed' => 0,
                    'pending' => 0,
                    'doc_received' => 0,
                    'reviewed' => 0,
                    'tax_filed' => 0,
                    'payment_collected' => 0,
                ];
            }
            $caretakersMap[$caretakerName]['total']++;
            if ($isDone) {
                $caretakersMap[$caretakerName]['completed']++;
            } else {
                $caretakersMap[$caretakerName]['pending']++;
            }
            if (($t['doc_status'] ?? '0') === '1') $caretakersMap[$caretakerName]['doc_received']++;
            if (($t['review1_status'] ?? '0') === '1') $caretakersMap[$caretakerName]['reviewed']++;
            if (($t['tax_status'] ?? '0') === '1') $caretakersMap[$caretakerName]['tax_filed']++;
            if (($t['payment_status'] ?? '0') === '1') $caretakersMap[$caretakerName]['payment_collected']++;
        }

        foreach ($caretakersMap as &$c) {
            $c['percent'] = $c['total'] > 0 ? (int)round(($c['completed'] / $c['total']) * 100) : 0;
        }
        unset($c);

        return [
            'total_customers'   => $totalCustomers,
            'doc_received'      => $docReceived,
            'completed'         => $completed,
            'reviewed'          => $reviewed,
            'tax_filed'         => $taxFiled,
            'payment_collected' => $paymentCollected,
            'payment_pending'   => max(0, $totalCustomers - $paymentCollected),
            'doc_received_pct'  => $totalCustomers > 0 ? (int)round(($docReceived / $totalCustomers) * 100) : 0,
            'completed_pct'     => $totalCustomers > 0 ? (int)round(($completed / $totalCustomers) * 100) : 0,
            'reviewed_pct'      => $totalCustomers > 0 ? (int)round(($reviewed / $totalCustomers) * 100) : 0,
            'tax_filed_pct'     => $totalCustomers > 0 ? (int)round(($taxFiled / $totalCustomers) * 100) : 0,
            'payment_pct'       => $totalCustomers > 0 ? (int)round(($paymentCollected / $totalCustomers) * 100) : 0,
            'caretakers'        => array_values($caretakersMap),
            'tasks_list'        => $tasks
        ];
    }
}