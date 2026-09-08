<?php
require_once '../app/models/Model.php';

class ClosingModel extends Model
{
    public function getClosingByFiscalId($fiscalId)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                c.customer_id, 
                c.customer_name, 
                c.fiscal_closing_date,
                cf.closing_id,
                cf.closing_status,
                cf.doc_status,
                cf.audit_status,
                cf.boj5_status,
                cf.dbd_efiling_status,
                cf.pnd50_status,
                u.user_firstname,
                u.user_lastname
            FROM tbl_fiscal_year_customers fyc
            INNER JOIN tbl_customers c ON fyc.customer_id = c.customer_id
            LEFT JOIN tbl_closing_financial cf ON fyc.fiscal_year_id = cf.fiscal_year_id
            LEFT JOIN tbl_user u ON fyc.user_id = u.user_id
            WHERE fyc.fiscal_id = :fiscal_id AND c.delete_at IS NULL
            ORDER BY c.customer_name ASC
        ");
        $stmt->execute(['fiscal_id' => $fiscalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
