<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

class MailService {
    private $mail;

    public function __construct() {
        // Ensure .env is loaded if not already
        if (class_exists('Dotenv\Dotenv') && !isset($_ENV['SMTP_HOST'])) {
            $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
            $dotenv->safeLoad();
        }

        $this->mail = new PHPMailer(true);
        try {
            // Server settings
            $this->mail->isSMTP();
            $this->mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = $_ENV['SMTP_USERNAME'] ?? '';
            $this->mail->Password   = $_ENV['SMTP_PASSWORD'] ?? '';
            $this->mail->SMTPSecure = $_ENV['SMTP_SECURE'] ?? PHPMailer::ENCRYPTION_SMTPS;
            
            $port = $_ENV['SMTP_PORT'] ?? 465;
            $this->mail->Port       = (int)$port;

            $this->mail->CharSet    = 'UTF-8';
            
            $fromEmail = $_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@example.com';
            $fromName  = $_ENV['MAIL_FROM_NAME'] ?? 'CPD AC System';
            $this->mail->setFrom($fromEmail, $fromName);

        } catch (Exception $e) {
            error_log("MailService initialization failed. Error: {$this->mail->ErrorInfo}");
        }
    }

    /**
     * Send email notification for new task assignment
     */
    public function sendTaskAssignmentEmail($toEmail, $toName, $taskTitle, $dueDate, $taskDetail = '') {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($toEmail, $toName);

            $this->mail->isHTML(true);
            $this->mail->Subject = "แจ้งเตือน: คุณได้รับมอบหมายงานใหม่ - " . $taskTitle;
            
            // Format Due Date (assuming Y-m-d format)
            $formattedDueDate = date('d/m/Y', strtotime($dueDate));
            $detailHtml = !empty($taskDetail) ? "<p><strong>รายละเอียด:</strong> " . htmlspecialchars($taskDetail) . "</p>" : "";

            $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;'>
                <h2 style='color: #2563eb;'>แจ้งเตือนการมอบหมายงานใหม่</h2>
                <p>สวัสดีคุณ {$toName},</p>
                <p>คุณได้รับมอบหมายงานใหม่ในระบบ CPD AC System ดังนี้:</p>
                
                <div style='background-color: #f8fafc; padding: 15px; border-left: 4px solid #3b82f6; margin: 20px 0;'>
                    <p style='margin: 0 0 10px 0;'><strong>ชื่องาน:</strong> " . htmlspecialchars($taskTitle) . "</p>
                    {$detailHtml}
                    <p style='margin: 0;'><strong>กำหนดส่ง:</strong> <span style='color: #dc2626; font-weight: bold;'>{$formattedDueDate}</span></p>
                </div>
                
                <p>กรุณาตรวจสอบรายละเอียดเพิ่มเติมและดำเนินการในระบบ</p>
                <br>
                <p style='color: #64748b; font-size: 12px;'>อีเมลฉบับนี้ส่งจากระบบอัตโนมัติ กรุณาอย่าตอบกลับ</p>
            </div>";

            $this->mail->Body = $body;
            $this->mail->AltBody = "แจ้งเตือนงานใหม่: {$taskTitle}\nกำหนดส่ง: {$formattedDueDate}\n\nกรุณาตรวจสอบในระบบ";

            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }
}
