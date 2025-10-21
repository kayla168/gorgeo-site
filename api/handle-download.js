import nodemailer from 'nodemailer';
import path from 'path';
import fs from 'fs';

// 主要修改点：移除了所有 filePath 值开头的 './'
const document_library = {
    'trouble_zones': {
        filePath: 'drop/GorgeoFasteners_6_Trouble_Zones_Checklist_2025.pdf', // <-- 已修改
        subject: 'Here is your requested guide: The "6 Trouble Zones" Checklist',
        body: "Hi there,<br><br>As requested, attached is your copy of the <strong>\"6 Hidden Trouble Zones in Conveyor Systems\"</strong> checklist.<br><br>This isn't a theoretical list. It's the exact field-tested tool our consultants use to diagnose the root cause of over 90% of common assembly failures. Use it to spot risks in your own designs before they become production problems.<br><br>Once the checklist helps you identify a potential trouble zone, the next step is to define a robust solution. Reply to this email with your drawing for a confidential review by our engineering team.<br><br>"
    },
    'Blind_Fit': {
        filePath: 'drop/GorgeoFasteners_Checklist_BlindFit_SleeveDesign.pdf', // <-- 已修改
        subject: 'Here is the "Blind-Fit Sleeve Design Checklist" you requested',
        body: "Hi there,<br><br>Thank you for requesting our technical resources. Attached is the <strong>\"Blind-Fit Sleeve Design Checklist\"</strong>.<br><br>This guide highlights 7 commonly missed features — from extraction grooves to insertion stops — that often turn blind fits into stuck or unserviceable joints, leading to costly downtime.<br><br>If you’re facing a specific sleeve or insert challenge, let's move from checklist to solution. Reply with your drawing for targeted feedback from our application engineers.<br><br>"
    },
    'pre_assembly': {
        filePath: 'drop/GorgeoFasteners_PreAssembly_Drawing_Checklist_2025.pdf', // <-- 已修改
        subject: 'Your Requested Pre-Assembly Drawing Checklist',
        body: "Hi there,<br><br>As requested, attached is your copy of the <strong>Pre-Assembly Drawing Checklist</strong>.<br><br>We developed this tool to pre-flight designs internally, catching minor oversights before they escalate into major rework or line-down situations. Use it to ensure your drawings are robust from the start.<br><br>If the checklist flags a potential issue, our engineers can help you find a solution. Reply with your drawing for a targeted analysis.<br><br>"
    },
    'tolerance': {
        filePath: 'drop/GorgeoFasteners_Fastener_Tolerance_Checklist_2025.pdf', // <-- 已修改
        subject: 'Your Requested Fastener Tolerance Checklist for Sorters',
        body: "Hi there,<br><br>Attached is your <strong>Fastener Tolerance Checklist</strong>, specifically tailored for high-speed sorter modules.<br><br>This checklist focuses on the geometric controls needed to prevent joint relaxation and subsequent re-torque events within the critical first 72 hours of operation — a common failure point in sortation systems.<br><br>When you're ready to lock in your design's long-term reliability, reply with your drawing for a detailed tolerance stack-up review.<br><br>"
    },
    'drop032': {
        filePath: 'drop/case-study-coating-jam-fit/GorgeoFasteners_CaseStudy_Coating_Jam_2025.pdf', // <-- 已修改
        subject: 'Your Requested Teardown: "CAD Passed, Coating Jammed" Case Study',
        body: "Hi there,<br><br>As requested, attached is the PDF teardown report: <strong>\"Case #032: CAD Passed, Coating Jammed the Fit\"</strong>.<br><br>This case study highlights how unmodeled variables like coating thickness can derail an otherwise sound design. It's a critical lesson in bridging the gap between digital models and physical reality.<br><br>If this analysis resonates with a challenge you're currently facing, let our engineers provide a second opinion. Reply with your drawing for a confidential, no-obligation review.<br><br>"
    }
};

const signature = `Best regards,<br>
<strong>Catherine Zhang</strong><br>
<span>Senior Assembly Fit Consultant</span><br>
<span>Structural Fit Reliability · ±0.01 mm</span><br>
<span>Gorgeo Fasteners | Sleeves · Pins · Locator Bolts</span>`;

export default async function handler(req, res) {
    if (req.method !== 'POST') {
        return res.status(405).json({ message: 'Method Not Allowed' });
    }
    try {
        const { email, document_type } = req.body;
        const current_doc = document_library[document_type];
        if (!email || !current_doc) {
            // 注意：这里的错误跳转路径最好和下面的保持一致，或者有专门的联系错误页
            return res.redirect(307, '/drop/error.html');
        }
        
        // 这里的代码现在可以非常可靠地工作了
        const filePath = path.join(process.cwd(), current_doc.filePath);
        
        if (!fs.existsSync(filePath)) {
            console.error(`Attachment file not found for doc_type '${document_type}'. Path: ${filePath}`);
            return res.redirect(307, '/drop/error.html');
        }

        const transporter = nodemailer.createTransport({
            host: process.env.SMTP_HOST,
            port: process.env.SMTP_PORT,
            secure: true,
            auth: {
                user: process.env.SMTP_USERNAME,
                pass: process.env.SMTP_PASSWORD,
            },
        });

        const full_body = `<div style="font-family: Calibri, sans-serif; font-size: 10.05pt; color: #000;">${current_doc.body}${signature}</div>`;
        
        await transporter.sendMail({
            from: `"${process.env.FROM_NAME}" <${process.env.FROM_EMAIL}>`,
            to: email,
            replyTo: `"${process.env.REPLY_TO_NAME}" <${process.env.FROM_EMAIL}>`,
            subject: current_doc.subject,
            html: full_body,
            attachments: [{
                filename: path.basename(filePath),
                path: filePath,
            }],
        });

        return res.redirect(307, '/drop/Checklist-Sent.html');
    } catch (error) {
        console.error('Mailer Error:', error);
        return res.redirect(307, '/drop/error.html');
    }
}