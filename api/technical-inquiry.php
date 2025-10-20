<?php
/**
 * Gorgeo Fasteners - 联系表单处理器
 * 功能: 接收表单数据，发送通知邮件给管理员，并自动回复客户。
 * 安全性: V2 - 敏感信息已移至外部配置文件。
 * 健壮性: V2 - 增加附件类型检查和详细错误日志。
 */

// 引入PHPMailer核心文件
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 使用 __DIR__ 确保路径的可靠性
require __DIR__ . '/../phpmailer/src/Exception.php';
require __DIR__ . '/../phpmailer/src/PHPMailer.php';
require __DIR__ . '/../phpmailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. 清理和验证输入
    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $company = isset($_POST["company"]) ? strip_tags(trim($_POST["company"])) : '';
    $message = strip_tags(trim($_POST["message"]));

    if (empty($name) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ../contact/error.html');
        exit("Invalid input.");
    }

    $mail = new PHPMailer(true);

    try {
        // 🔒 改进：从外部文件安全地加载服务器配置
        $config = require __DIR__ . '/config/config.php';

        $mail->isSMTP();
        $mail->CharSet    = 'UTF-8';
        $mail->Host       = $config['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['smtp_username'];
        $mail->Password   = $config['smtp_password']; // <-- 安全！从配置文件读取
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $config['smtp_port'];

        // --- 1. 发送通知邮件给管理员 ---
        $mail->setFrom($config['from_email'], $name . ' (Website Inquiry)');
        $mail->addAddress($config['from_email'], 'Catherine Zhang');
        $mail->addReplyTo($email, $name);

        // 🔧 改进：增加附件安全检查 (文件类型和大小)
        if (isset($_FILES['drawing']) && $_FILES['drawing']['error'] == UPLOAD_ERR_OK) {
            $file_size = $_FILES['drawing']['size'];
            $file_name = $_FILES['drawing']['name'];
            $file_tmp_name = $_FILES['drawing']['tmp_name'];
            $allowed_extensions = ['pdf', 'dwg', 'dxf', 'step', 'stp', 'iges', 'igs', 'jpg', 'jpeg', 'png', 'zip', 'rar'];
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($file_size <= 5 * 1024 * 1024 && in_array($file_extension, $allowed_extensions)) {
                $mail->addAttachment($file_tmp_name, $file_name);
            }
        }

        $mail->isHTML(true);
        $mail->Subject = "New Technical Inquiry from {$name}" . ($company ? " ({$company})" : "");
        $mail->Body = "<strong>Name:</strong> " . nl2br(htmlspecialchars($name)) . "<br>" .
                      "<strong>Email:</strong> " . htmlspecialchars($email) . "<br>" .
                      "<strong>Company:</strong> " . nl2br(htmlspecialchars($company)) . "<br>" .
                      "<strong>Message:</strong><br>" . nl2br(htmlspecialchars($message));

        $mail->send();

        // --- 2. 发送自动回信给客户 ---
        $mail->clearAllRecipients();
        $mail->clearAttachments();
        $mail->clearReplyTos();

        $mail->setFrom($config['from_email'], 'Catherine Zhang | Gorgeo Fasteners');
        $mail->addAddress($email, $name);
        $mail->Subject = "Confirmation: We've received your inquiry [Analysis in Progress]";
        
        $signature = "Best regards,<br>
<strong>Catherine Zhang</strong><br>
<span>Senior Assembly Fit Consultant</span><br>
<span>Structural Fit Reliability · ±0.01 mm</span><br>
<span>Gorgeo Fasteners | Sleeves · Pins · Locator Bolts</span>";
        
        // (自动回复邮件的HTML内容保持不变)
        $mail->Body = "
        <div style='font-family: Calibri, sans-serif; font-size: 11pt; color: #333; line-height: 1.5;'>
            <p>Hi " . htmlspecialchars($name) . ",</p>
            <p>This is an automatic confirmation that we have successfully received your inquiry and any attached drawings. Thank you for reaching out.</p>
            <p>Our engineering team will personally review your message and get back to you within one business day. Please rest assured that all submitted files are handled with complete confidentiality.</p>
            <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
            <p><strong>While you wait, explore how we solve similar challenges:</strong></p>
            <ul style='padding-left: 0; list-style: none;'>
                <li style='margin-bottom: 10px;'>
                    <a href='https://www.gorgeofasteners.com/blog/vibration-loosening-fix/vibration-loosening-fix.html' style='color: #007bff; text-decoration: none;'>
                        <strong>Case Study: Fixing Chronic Vibration Loosening</strong><br>
                        <span style='color: #555; font-size: 0.9em;'>How we use structural geometry, not just torque, to create joints that never back out.</span>
                    </a>
                </li>
                <li>
                    <a href='https://www.gorgeofasteners.com/blog/coating-induced-jam-fit/coating-induced-jam-fit.html' style='color: #007bff; text-decoration: none;'>
                        <strong>Teardown: When a 0.05mm Coating Jams Assembly</strong><br>
                        <span style='color: #555; font-size: 0.9em;'>Dissecting how an unmodeled finish layer can turn a perfect CAD fit into a production-line failure.</span>
                    </a>
                </li>
            </ul>
            <br>
            <p>{$signature}</p>
            <p style='font-size: 0.85em; color: #777; margin-top: 25px;'>
                P.S. If you need to add any information to your inquiry, simply reply to this email. For truly urgent matters, you can find our direct contact details on our website.
            </p>
        </div>";

        $mail->send();
        header('Location:../contact/thank-you.html');
        exit();

    } catch (Exception $e) {
        // 🔧 改进：启用详细的错误日志
        error_log("Contact Form Error for {$email}: {$mail->ErrorInfo}");
        header('Location:../contact/error.html');
        exit();
    }
} else {
    http_response_code(403);
    exit("Invalid request method.");
}
?>