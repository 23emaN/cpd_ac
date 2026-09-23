<?php
require_once '../app/models/Model.php';

class ClosingModel extends Model
{
    public function getClosingByFiscalId($fiscalId)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                fyc.fiscal_year_id,
                fyc.user_id,
                c.customer_id, 
                c.customer_name, 
                c.fiscal_closing_date,
                cf.closing_id,
                cf.closing_status,
                cf.closing_date,
                cf.doc_status,
                cf.doc_date,
                cf.audit_status,
                cf.audit_date,
                cf.budget_refund_date,
                cf.boj5_status,
                cf.boj5_date,
                cf.dbd_efiling_status,
                cf.dbd_efiling_date,
                cf.pnd50_status,
                cf.pnd50_date,
                u.user_firstname,
                u.user_lastname,
                acc_rd.account_user_name as rd_user_name,
                acc_rd.account_password as rd_password,
                acc_dbd.account_user_name as dbd_user_name,
                acc_dbd.account_password as dbd_password
            FROM tbl_fiscal_year_customers fyc
            INNER JOIN tbl_customers c ON fyc.customer_id = c.customer_id
            LEFT JOIN tbl_closing_financial cf ON fyc.fiscal_year_id = cf.fiscal_year_id
            LEFT JOIN tbl_user u ON fyc.user_id = u.user_id
            LEFT JOIN tbl_customer_accounts acc_rd 
                   ON acc_rd.fiscal_year_id = fyc.fiscal_year_id 
                  AND acc_rd.customer_id = c.customer_id 
                  AND acc_rd.account_name = 'กรมสรรพากร'
            LEFT JOIN tbl_customer_accounts acc_dbd 
                   ON acc_dbd.fiscal_year_id = fyc.fiscal_year_id 
                  AND acc_dbd.customer_id = c.customer_id 
                  AND acc_dbd.account_name = 'กรมพัฒนาธุรกิจการค้า'
            WHERE fyc.fiscal_id = :fiscal_id AND c.delete_at IS NULL
            ORDER BY c.customer_name ASC
        ");
        $stmt->execute(['fiscal_id' => $fiscalId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch all accounts for customers in this fiscal year
        $stmtAccounts = $this->pdo->prepare("
            SELECT customer_id, account_name, account_user_name, account_password 
            FROM tbl_customer_accounts 
            WHERE fiscal_year_id IN (SELECT fiscal_year_id FROM tbl_fiscal_year_customers WHERE fiscal_id = :fiscal_id)
        ");
        $stmtAccounts->execute(['fiscal_id' => $fiscalId]);
        $accounts = $stmtAccounts->fetchAll(PDO::FETCH_ASSOC);
        
        $accountsMap = [];
        foreach ($accounts as $acc) {
            $accountsMap[$acc['customer_id']][] = $acc;
        }
        
        foreach ($rows as &$row) {
            $row['accounts'] = $accountsMap[$row['customer_id']] ?? [];
        }
        
        return $rows;
    }

    public function getCaretakersByFiscalId($fiscalId)
    {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT u.user_id, u.user_firstname, u.user_lastname
            FROM tbl_fiscal_year_customers fyc
            INNER JOIN tbl_customers c ON fyc.customer_id = c.customer_id
            INNER JOIN tbl_user u ON fyc.user_id = u.user_id
            WHERE fyc.fiscal_id = :fiscal_id AND c.delete_at IS NULL
            ORDER BY u.user_firstname ASC, u.user_lastname ASC
        ");
        $stmt->execute(['fiscal_id' => $fiscalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateClosingData($data)
    {
        // Convert dates from DD/MM/YYYY to YYYY-MM-DD
        $formatDate = function($dateStr) {
            if (empty($dateStr)) return null;
            $parts = explode('/', $dateStr);
            if (count($parts) === 3) {
                return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            }
            return null;
        };

        $closing_id = !empty($data['closing_id']) ? $data['closing_id'] : null;
        $fiscal_year_id = !empty($data['fiscal_year_id']) ? $data['fiscal_year_id'] : null;

        if (!$fiscal_year_id && !$closing_id) {
            throw new Exception("Missing identification for closing data update.");
        }

        $params = [
            'doc_status' => !empty($data['doc_status']) ? '1' : '0',
            'doc_date' => !empty($data['doc_status']) ? $formatDate($data['doc_date'] ?? '') : null,
            'closing_status' => !empty($data['closing_status']) ? '1' : '0',
            'closing_date' => !empty($data['closing_status']) ? $formatDate($data['closing_date'] ?? '') : null,
            'audit_status' => !empty($data['audit_status']) ? '1' : '0',
            'audit_date' => !empty($data['audit_status']) ? $formatDate($data['audit_date'] ?? '') : null,
            'budget_refund_date' => !empty($data['budget_refund_date']) ? $formatDate($data['budget_refund_date'] ?? '') : null,
            'boj5_status' => !empty($data['boj5_status']) ? '1' : '0',
            'boj5_date' => !empty($data['boj5_status']) ? $formatDate($data['boj5_date'] ?? '') : null,
            'dbd_efiling_status' => !empty($data['dbd_efiling_status']) ? '1' : '0',
            'dbd_efiling_date' => !empty($data['dbd_efiling_status']) ? $formatDate($data['dbd_efiling_date'] ?? '') : null,
            'pnd50_status' => !empty($data['pnd50_status']) ? '1' : '0',
            'pnd50_date' => !empty($data['pnd50_status']) ? $formatDate($data['pnd50_date'] ?? '') : null,
        ];

        if ($closing_id) {
            $sql = "UPDATE tbl_closing_financial SET
                    closing_status = :closing_status,
                    closing_date = :closing_date,
                    doc_status = :doc_status,
                    doc_date = :doc_date,
                    audit_status = :audit_status,
                    audit_date = :audit_date,
                    budget_refund_date = :budget_refund_date,
                    boj5_status = :boj5_status,
                    boj5_date = :boj5_date,
                    dbd_efiling_status = :dbd_efiling_status,
                    dbd_efiling_date = :dbd_efiling_date,
                    pnd50_status = :pnd50_status,
                    pnd50_date = :pnd50_date
                    WHERE closing_id = :closing_id";
            $params['closing_id'] = $closing_id;
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } else {
            $sql = "INSERT INTO tbl_closing_financial (
                        fiscal_year_id,
                        closing_status, 
                        closing_date, 
                        doc_status, 
                        doc_date, 
                        audit_status, 
                        audit_date, 
                        budget_refund_date, 
                        boj5_status, 
                        boj5_date, 
                        dbd_efiling_status, 
                        dbd_efiling_date, 
                        pnd50_status, 
                        pnd50_date, 
                        created_at
                    ) VALUES (
                        :fiscal_year_id,
                        :closing_status, 
                        :closing_date, 
                        :doc_status, 
                        :doc_date, 
                        :audit_status, 
                        :audit_date, 
                        :budget_refund_date, 
                        :boj5_status, 
                        :boj5_date, 
                        :dbd_efiling_status, 
                        :dbd_efiling_date, 
                        :pnd50_status, :pnd50_date, NOW()
                    )";
            $params['fiscal_year_id'] = $fiscal_year_id;
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        }
    }
}
