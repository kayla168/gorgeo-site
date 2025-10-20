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

$document_library = [
    'trouble_zones' => [
        'file_path' => __DIR__ . '/../drop/GorgeoFasteners_6_Trouble_Zones_Checklist_2025.pdf',
        'subject'   => 'Your PDF: “6 Hidden Trouble Zones” in Conveyor Fastening',
        'body'      => "Hi there,<br><br>Attached is your copy of the <strong>“6 Hidden Trouble Zones”</strong> checklist for conveyor systems.<br><br>This guide highlights the six failure-prone areas we see most often in real-world assembly reviews—each one subtle enough to pass initial checks, yet serious enough to shut down a line.<br><br>Use this as a mirror against your own drawings. If you spot a match—or even a maybe—just reply with the drawing. We’ll run it through our diagnostic lens, no obligation.<br><br>"
    ],
    'drop032_blog' => [
        'file_path' => __DIR__ . '/../drop/case-study-coating-jam-fit/GorgeoFasteners_CaseStudy_Coating_Jam_2025.pdf',
        'subject'   => 'Teardown Inside: “CAD Passed. Coating Jammed.”',
        'body'      => "Hi there,<br><br>Attached is the detailed teardown: <strong>Case Study #032 – Coating Jammed the Fit</strong>.<br><br>This is one of those classic gotchas—CAD was green, tolerance looked perfect, yet the part seized during final assembly due to an unmodeled 0.05mm coating.<br><br>If you've ever had a part fail “for no reason,” this breakdown may feel familiar. Want to stress-test your own drawing against this type of trap? Just reply with it—we’ll run a full pre-production diagnosis, free.<br><br>"
    ],
    'vibration_checklist' => [
        'file_path' => __DIR__ . '/../drop/GorgeoFasteners_Anti-Vibration_Design_Checklist_2025.pdf',
        'subject'   => 'PDF Attached: The Anti-Vibration Fastener Checklist',
        'body'      => "Hi there,<br><br>Here’s your copy of the <strong>Anti-Vibration Design Checklist</strong>.<br><br>This isn’t just about preventing loosening—it’s about shifting from patchwork fixes (like threadlocker) to real, geometry-based stability. Use this to audit vulnerable joints in high-vibration zones.<br><br>Flagged a weak point? Forward your drawing and we’ll walk through it with a geometry-first lens. It’s often not the torque—it’s the path.<br><br>"
    ],
    'tolerance_checklist' => [
        'file_path' => __DIR__ . '/../drop/GorgeoFasteners_Fastener_Tolerance_Checklist_2025.pdf',
        'subject'   => 'Checklist Delivered: Fastener Tolerance Pre-Check',
        'body'      => "Hi there,<br><br>Your copy of the <strong>Fastener Fit Tolerance Checklist</strong> is attached.<br><br>This is the internal tool we use to stress-test drawings before anything hits the machine. It's built to catch hidden stack-ups, unmodeled coatings, and spec-compliant failures that cause field jamming.<br><br>If anything here rings alarm bells in your current design, just reply with your drawing—we'll dissect it from the fit path outward.<br><br>"
    ],
    'pre_assembly' => [
        'file_path' => __DIR__ . '/../drop/GorgeoFasteners_PreAssembly_Drawing_Checklist_2025.pdf',
        'subject'   => 'Your Pre-Assembly Drawing Checklist is Ready',
        'body'      => "Hi there,<br><br>As promised, here’s your copy of the <strong>Pre-Assembly Drawing Checklist</strong>.<br><br>This tool was developed for our own internal reviews—to spot the kind of small geometric oversights that turn into big-line failures. It’s quick to use and often catches what spec checks miss.<br><br>If any section raises a red flag on your side, just reply with your drawing. We’ll run a full diagnostic at no cost, and flag what others often overlook.<br><br>"
    ],
// 在 $document_library 数组中
'prototype_report' => [
    'file_path' => __DIR__ . '/../drop/GorgeoFasteners_Structural_Improvement_Suggestions_Report_2025.pdf',
    'subject'   => 'Your Sample Prototype Report is Attached',
    'body'      => "Hi there,<br><br>Here’s the sample report from a real-world prototype build.<br><br>It walks through how we correlate physical measurements with structural risks—and more importantly, how small design tweaks can prevent costly rework at full scale.<br><br>If you’ve got a drawing or sample you'd like us to stress-test the same way, just reply—we’ll walk through it from a geometry-first lens.<br><br>"
],

'assembly_audit_report' => [
    'file_path' => __DIR__ . '/../drop/GorgeoFasteners_Structural_Improvement_Suggestions_Report_2025.pdf',
    'subject'   => 'Your Sample Assembly Audit Report',
    'body'      => "Hi there,<br><br>Attached is your copy of the sample <strong>Assembly Audit Report</strong>.<br><br>It shows how we reverse-engineer failures from the final assembly backward—linking symptoms like misalignment, jamming, or micro-shifts to subtle geometric root causes.<br><br>If your team is chasing a “mystery failure” right now, send over the drawing or part photo—we’ll run a full fit-path diagnosis, no charge.<br><br>"
],

// ✅ 新增：为“错位案例研究 (CMM合格但装配卡死)”添加的邮件文案--apolications
    'misalignment_case_study' => [
        'file_path' => __DIR__ . '/../drop/GorgeoFasteners_Case_Misalignment_Sleeve_2025.pdf',
        'subject'   => 'Case Study Attached: Why CMM-Passed Parts Still Jam',
        'body'      => "Hi there,<br><br>As requested, here is your copy of the <strong>Case Study: Misalignment Sleeve Failure</strong>.<br><br>This is the deep dive into the exact problem from the article: how a part can pass every CMM check and meet H7/g6 specs, yet still jam on the assembly line due to hidden geometric traps.<br><br>The sketches and diagnostic checklist inside are the same tools we used to pinpoint the root cause. If this scenario feels too familiar, just reply to this email with your drawing. We’ll provide a complimentary fit diagnosis.<br><br>"
    ],

 'anti_vibration_checklist' => [
    'file_path' => __DIR__ . '/../drop/GorgeoFasteners_Anti-Vibration_Design_Checklist_2025.pdf',
    'subject'   => 'Checklist Attached: Preventing Fatigue Failure at Its Source',
    'body'      => "Hi there,<br><br>Here is your copy of the <strong>Anti-Vibration Design Checklist</strong>, as requested.<br><br>As the article on flange failure demonstrated, a catastrophic fatigue fracture is often the final, loudest symptom of chronic, unaddressed vibration and preload loss.<br><br>This checklist provides a geometry-first approach to diagnosing and fixing those root causes. Use it to audit your own joints for the hidden risks—like eccentric loading and micro-slip—that lead to failure. If you check off more than two boxes, reply to this email with your drawing. We'll provide a complimentary analysis of its stability under dynamic loads.<br><br>"
],

'vibration_fixes' => [
    'file_path' => __DIR__ . '/../drop/GorgeoFasteners_Anti-Vibration_Design_Checklist_2025.pdf',
    'subject'   => 'Your Guide Attached: The Geometry Behind the AGV Loosening Fix',
    'body'      => "Hi there,<br><br>Here is your copy of the <strong>Anti-Vibration Design Checklist</strong>.<br><br>As the AGV hub article demonstrated, the most robust way to prevent loosening is through intelligent geometry, not just more torque or friction. The wedge-lock thread is an advanced application of these fundamental principles.<br><br>This checklist breaks down those foundational principles. Use it to audit any joint under dynamic load for the hidden risks—like micro-slip and preload decay—that traditional split washers and locknuts simply can't solve. If you find your current designs flag multiple risks, reply to this email with the drawing. We'll provide a complimentary analysis of how to apply these geometric principles to your specific application.<br><br>"
],


//新增为：为“(错位案例研究 CMM合格但装配卡死)”添加的邮件文案--侧边栏

// ✅ 新增：为侧边栏 "Fix Kits" 添加的邮件文案
'coating_fit_jam_kit' => [
    'file_path' => __DIR__ . '/../drop/fix/GorgeoFasteners_Case_Misalignment_Sleeve_2025.pdf',
    'subject'   => 'Your Fix Kit Attached: Coating-Induced Fit Jam',
    'body'      => "Hi there,<br><br>As requested, here is your copy of the <strong>Fix Kit for Coating-Induced Fit Jams</strong>.<br><br>This case study is the perfect tool for this problem, as it dissects how parts that pass all inspections can still fail due to unmodeled geometric factors—like coatings.<br><br>Use the diagnostic principles inside to stress-test your own designs against these hidden traps. If you spot a potential risk, just reply to this email with your drawing for a complimentary geometric diagnosis.<br><br>"
],

'stress_path_analysis_kit' => [
    'file_path' => __DIR__ . '/../drop/fix/GorgeoFasteners_VibrationLoosening_Fixes.pdf',
    'subject'   => 'Your Fix Kit Attached: Stress Path Misfit Analysis',
    'body'      => "Hi there,<br><br>Attached is your copy of the <strong>Fix Kit for Stress Path Misfit Analysis</strong>.<br><br>A stress path misfit is the root cause of many fatigue and vibration failures. This guide provides the geometry-first principles needed to audit your joints and ensure forces are transmitted exactly as intended.<br><br>Use this checklist to identify high-risk areas in your dynamic assemblies. If you have any concerns, reply with your drawing, and we will provide a complimentary dynamic load analysis.<br><br>"
],







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
        error_log("Attachment file not found for doc_type '{$doc_type}'. Path: {$current_doc['file_path']}");
        header('Location: ../../blog/error.html'); // Corrected redirect path
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
        
        header('Location: ../blog/casestudy-sent.html');
        exit();

    } catch (Exception $e) {
        // 🔧 改进：启用详细的错误日志
        error_log("Mailer Error for {$email} requesting {$doc_type}: {$mail->ErrorInfo}");
        header('Location: ../../blog/error.html');
        exit();
    }
} else {
    header("HTTP/1.0 405 Method Not Allowed");
    exit();
}
?>