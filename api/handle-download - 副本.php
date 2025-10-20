<?php
/**
 * Gorgeo Fasteners - 统一文档下载处理器
 * 功能: 根据表单提交的文档类型，自动发送包含相应附件的邮件。
 * 安全性: V4 - 敏感信息已移至外部配置文件。
 * 健壮性: V4 - 增加文件存在性检查和详细错误日志。
 */

// 引入PHPMailer核心文件
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 使用 __DIR__ 确保路径的可靠性
require __DIR__ . '/../phpmailer/src/Exception.php';
require __DIR__ . '/../phpmailer/src/PHPMailer.php';
require __DIR__ . '/../phpmailer/src/SMTP.php';

// --- 核心配置：用一个数组管理所有文档 ---
$document_library = [
    'trouble_zones' => [
        'file_path' => __DIR__ . '/../drop/GorgeoFasteners_6_Trouble_Zones_Checklist_2025.pdf',
        'subject'   => 'Here is your requested guide: The "6 Trouble Zones" Checklist',
        'body'      => "Hi there,<br><br>As requested, attached is your copy of the <strong>\"6 Hidden Trouble Zones in Conveyor Systems\"</strong> checklist.<br><br>This isn't a theoretical list. It's the exact field-tested tool our consultants use to diagnose the root cause of over 90% of common assembly failures. Use it to spot risks in your own designs before they become production problems.<br><br>Once the checklist helps you identify a potential trouble zone, the next step is to define a robust solution. Reply to this email with your drawing for a confidential review by our engineering team.<br><br>"
    ], // ✅ 修正：添加了逗号
    'Blind_Fit' => [
        'file_path' => __DIR__ . '/../drop/GorgeoFasteners_Checklist_BlindFit_SleeveDesign.pdf',
        'subject'   => 'Here is the "Blind-Fit Sleeve Design Checklist" you requested',
        'body'      => "Hi there,<br><br>Thank you for requesting our technical resources. Attached is the <strong>\"Blind-Fit Sleeve Design Checklist\"</strong>.<br><br>This guide highlights 7 commonly missed features — from extraction grooves to insertion stops — that often turn blind fits into stuck or unserviceable joints, leading to costly downtime.<br><br>If you’re facing a specific sleeve or insert challenge, let's move from checklist to solution. Reply with your drawing for targeted feedback from our application engineers.<br><br>"
    ], // ✅ 修正：添加了逗号
    'pre_assembly' => [
        'file_path' => __DIR__ . '/../drop/GorgeoFasteners_PreAssembly_Drawing_Checklist_2025.pdf',
        'subject'   => 'Your Requested Pre-Assembly Drawing Checklist',
        'body'      => "Hi there,<br><br>As requested, attached is your copy of the <strong>Pre-Assembly Drawing Checklist</strong>.<br><br>We developed this tool to pre-flight designs internally, catching minor oversights before they escalate into major rework or line-down situations. Use it to ensure your drawings are robust from the start.<br><br>If the checklist flags a potential issue, our engineers can help you find a solution. Reply with your drawing for a targeted analysis.<br><br>"
    ], // ✅ 修正：添加了逗号
    'tolerance' => [
        'file_path' => __DIR__ . '/../drop/GorgeoFasteners_Fastener_Tolerance_Checklist_2025.pdf',
        'subject'   => 'Your Requested Fastener Tolerance Checklist for Sorters',
        'body'      => "Hi there,<br><br>Attached is your <strong>Fastener Tolerance Checklist</strong>, specifically tailored for high-speed sorter modules.<br><br>This checklist focuses on the geometric controls needed to prevent joint relaxation and subsequent re-torque events within the critical first 72 hours of operation — a common failure point in sortation systems.<br><br>When you're ready to lock in your design's long-term reliability, reply with your drawing for a detailed tolerance stack-up review.<br><br>"
    ], // ✅ 修正：添加了逗号
    'drop032' => [
        'file_path' => __DIR__ . '/../drop/case-study-coating-jam-fit/GorgeoFasteners_CaseStudy_Coating_Jam_2025.pdf',
        'subject'   => 'Your Requested Teardown: "CAD Passed, Coating Jammed" Case Study',
        'body'      => "Hi there,<br><br>As requested, attached is the PDF teardown report: <strong>\"Case #032: CAD Passed, Coating Jammed the Fit\"</strong>.<br><br>This case study highlights how unmodeled variables like coating thickness can derail an otherwise sound design. It's a critical lesson in bridging the gap between digital models and physical reality.<br><br>If this analysis resonates with a challenge you're currently facing, let our engineers provide a second opinion. Reply with your drawing for a confidential, no-obligation review.<br><br>"
    ]
];

// --- 业务逻辑：处理请求 ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. 获取并清理输入
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $doc_type = isset($_POST['document_type']) ? trim($_POST['document_type']) : '';

    // 2. 验证输入
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !array_key_exists($doc_type, $document_library)) {
        header('Location: ../contact/error.html');
        exit("Invalid input.");
    }

    $current_doc = $document_library[$doc_type];

    // 🔧 改进：在发送邮件前，检查附件文件是否存在
    if (!file_exists($current_doc['file_path'])) {
        // 记录详细错误供自己排查，但给用户看通用错误页
        error_log("Attachment file not found for doc_type '{$doc_type}'. Path: {$current_doc['file_path']}");
        header('Location: ../drop/error.html');
        exit("Attachment missing on server.");
    }

    // 4. 封装签名和 HTML 样式
    $signature = "Best regards,<br>
<strong>Catherine Zhang</strong><br>
<span>Senior Assembly Fit Consultant</span><br>
<span>Structural Fit Reliability · ±0.01 mm</span><br>
<span>Gorgeo Fasteners | Sleeves · Pins · Locator Bolts</span>";

    $full_body = "<div style=\"font-family: Calibri, sans-serif; font-size: 10.05pt; color: #000;\">" .
                 $current_doc['body'] .
                 $signature .
                 "</div>";

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

        // --- 邮件内容配置 ---
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($email);
        $mail->addReplyTo($config['from_email'], $config['reply_to_name']);
        
        $mail->addAttachment($current_doc['file_path']);
        $mail->Subject = $current_doc['subject'];
        $mail->Body    = $full_body;
        $mail->isHTML(true);
        
        $mail->send();
        
        header('Location: ../drop/Checklist-Sent.html');
        exit();

    } catch (Exception $e) {
        // 🔧 改进：启用错误日志，以便排查发送失败的原因
        error_log("Mailer Error for {$email} requesting {$doc_type}: {$mail->ErrorInfo}");
        header('Location: ../drop/error.html');
        exit();
    }
} else {
    header("HTTP/1.0 405 Method Not Allowed");
    exit();
}
?>