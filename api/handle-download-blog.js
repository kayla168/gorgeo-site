import nodemailer from 'nodemailer';
import path from 'path';
import fs from 'fs';

// 主要修改点：移除了所有 filePath 值开头的 './'
const document_library = {
    'trouble_zones': {
        filePath: 'drop/GorgeoFasteners_6_Trouble_Zones_Checklist_2025.pdf',
        subject: 'Your PDF: “6 Hidden Trouble Zones” in Conveyor Fastening',
        body: "Hi there,<br><br>Attached is your copy of the <strong>“6 Hidden Trouble Zones”</strong> checklist for conveyor systems.<br><br>This guide highlights the six failure-prone areas we see most often in real-world assembly reviews—each one subtle enough to pass initial checks, yet serious enough to shut down a line.<br><br>Use this as a mirror against your own drawings. If you spot a match—or even a maybe—just reply with the drawing. We’ll run it through our diagnostic lens, no obligation.<br><br>"
    },
    'drop032_blog': {
        filePath: 'drop/case-study-coating-jam-fit/GorgeoFasteners_CaseStudy_Coating_Jam_2025.pdf',
        subject: 'Teardown Inside: “CAD Passed. Coating Jammed.”',
        body: "Hi there,<br><br>Attached is the detailed teardown: <strong>Case Study #032 – Coating Jammed the Fit</strong>.<br><br>This is one of those classic gotchas—CAD was green, tolerance looked perfect, yet the part seized during final assembly due to an unmodeled 0.05mm coating.<br><br>If you've ever had a part fail “for no reason,” this breakdown may feel familiar. Want to stress-test your own drawing against this type of trap? Just reply with it—we’ll run a full pre-production diagnosis, free.<br><br>"
    },
    'vibration_checklist': {
        filePath: 'drop/GorgeoFasteners_Anti-Vibration_Design_Checklist_2025.pdf',
        subject: 'PDF Attached: The Anti-Vibration Fastener Checklist',
        body: "Hi there,<br><br>Here’s your copy of the <strong>Anti-Vibration Design Checklist</strong>.<br><br>This isn’t just about preventing loosening—it’s about shifting from patchwork fixes (like threadlocker) to real, geometry-based stability. Use this to audit vulnerable joints in high-vibration zones.<br><br>Flagged a weak point? Forward your drawing and we’ll walk through it with a geometry-first lens. It’s often not the torque—it’s the path.<br><br>"
    },
    'tolerance_checklist': {
        filePath: 'drop/GorgeoFasteners_Fastener_Tolerance_Checklist_2025.pdf',
        subject: 'Checklist Delivered: Fastener Tolerance Pre-Check',
        body: "Hi there,<br><br>Your copy of the <strong>Fastener Fit Tolerance Checklist</strong> is attached.<br><br>This is the internal tool we use to stress-test drawings before anything hits the machine. It's built to catch hidden stack-ups, unmodeled coatings, and spec-compliant failures that cause field jamming.<br><br>If anything here rings alarm bells in your current design, just reply with your drawing—we'll dissect it from the fit path outward.<br><br>"
    },
    'pre_assembly': {
        filePath: 'drop/GorgeoFasteners_PreAssembly_Drawing_Checklist_2025.pdf',
        subject: 'Your Pre-Assembly Drawing Checklist is Ready',
        body: "Hi there,<br><br>As promised, here’s your copy of the <strong>Pre-Assembly Drawing Checklist</strong>.<br><br>This tool was developed for our own internal reviews—to spot the kind of small geometric oversights that turn into big-line failures. It’s quick to use and often catches what spec checks miss.<br><br>If any section raises a red flag on your side, just reply with your drawing. We’ll run a full diagnostic at no cost, and flag what others often overlook.<br><br>"
    },
    'prototype_report': {
        filePath: 'drop/GorgeoFasteners_Structural_Improvement_Suggestions_Report_2025.pdf',
        subject: 'Your Sample Prototype Report is Attached',
        body: "Hi there,<br><br>Here’s the sample report from a real-world prototype build.<br><br>It walks through how we correlate physical measurements with structural risks—and more importantly, how small design tweaks can prevent costly rework at full scale.<br><br>If you’ve got a drawing or sample you'd like us to stress-test the same way, just reply—we’ll walk through it from a geometry-first lens.<br><br>"
    },
    'assembly_audit_report': {
        filePath: 'drop/GorgeoFasteners_Structural_Improvement_Suggestions_Report_2025.pdf',
        subject: 'Your Sample Assembly Audit Report',
        body: "Hi there,<br><br>Attached is your copy of the sample <strong>Assembly Audit Report</strong>.<br><br>It shows how we reverse-engineer failures from the final assembly backward—linking symptoms like misalignment, jamming, or micro-shifts to subtle geometric root causes.<br><br>If your team is chasing a “mystery failure” right now, send over the drawing or part photo—we’ll run a full fit-path diagnosis, no charge.<br><br>"
    },
    'misalignment_case_study': {
        filePath: 'drop/GorgeoFasteners_Case_Misalignment_Sleeve_2025.pdf',
        subject: 'Case Study Attached: Why CMM-Passed Parts Still Jam',
        body: "Hi there,<br><br>As requested, here is your copy of the <strong>Case Study: Misalignment Sleeve Failure</strong>.<br><br>This is the deep dive into the exact problem from the article: how a part can pass every CMM check and meet H7/g6 specs, yet still jam on the assembly line due to hidden geometric traps.<br><br>The sketches and diagnostic checklist inside are the same tools we used to pinpoint the root cause. If this scenario feels too familiar, just reply to this email with your drawing. We’ll provide a complimentary fit diagnosis.<br><br>"
    },
    'anti_vibration_checklist': {
        filePath: 'drop/GorgeoFasteners_Anti-Vibration_Design_Checklist_2025.pdf',
        subject: 'Checklist Attached: Preventing Fatigue Failure at Its Source',
        body: "Hi there,<br><br>Here is your copy of the <strong>Anti-Vibration Design Checklist</strong>, as requested.<br><br>As the article on flange failure demonstrated, a catastrophic fatigue fracture is often the final, loudest symptom of chronic, unaddressed vibration and preload loss.<br><br>This checklist provides a geometry-first approach to diagnosing and fixing those root causes. Use it to audit your own joints for the hidden risks—like eccentric loading and micro-slip—that lead to failure. If you check off more than two boxes, reply to this email with your drawing. We'll provide a complimentary analysis of its stability under dynamic loads.<br><br>"
    },
    'vibration_fixes': {
        filePath: 'drop/GorgeoFasteners_Anti-Vibration_Design_Checklist_2025.pdf',
        subject: 'Your Guide Attached: The Geometry Behind the AGV Loosening Fix',
        body: "Hi there,<br><br>Here is your copy of the <strong>Anti-Vibration Design Checklist</strong>.<br><br>As the AGV hub article demonstrated, the most robust way to prevent loosening is through intelligent geometry, not just more torque or friction. The wedge-lock thread is an advanced application of these fundamental principles.<br><br>This checklist breaks down those foundational principles. Use it to audit any joint under dynamic load for the hidden risks—like micro-slip and preload decay—that traditional split washers and locknuts simply can't solve. If you find your current designs flag multiple risks, reply to this email with the drawing. We'll provide a complimentary analysis of how to apply these geometric principles to your specific application.<br><br>"
    },
    'coating_fit_jam_kit': {
        filePath: 'drop/fix/GorgeoFasteners_Case_Misalignment_Sleeve_2025.pdf',
        subject: 'Your Fix Kit Attached: Coating-Induced Fit Jam',
        body: "Hi there,<br><br>As requested, here is your copy of the <strong>Fix Kit for Coating-Induced Fit Jams</strong>.<br><br>This case study is the perfect tool for this problem, as it dissects how parts that pass all inspections can still fail due to unmodeled geometric factors—like coatings.<br><br>Use the diagnostic principles inside to stress-test your own designs against these hidden traps. If you spot a potential risk, just reply to this email with your drawing for a complimentary geometric diagnosis.<br><br>"
    },
    'stress_path_analysis_kit': {
        filePath: 'drop/fix/GorgeoFasteners_VibrationLoosening_Fixes.pdf',
        subject: 'Your Fix Kit Attached: Stress Path Misfit Analysis',
        body: "Hi there,<br><br>Attached is your copy of the <strong>Fix Kit for Stress Path Misfit Analysis</strong>.<br><br>A stress path misfit is the root cause of many fatigue and vibration failures. This guide provides the geometry-first principles needed to audit your joints and ensure forces are transmitted exactly as intended.<br><br>Use this checklist to identify high-risk areas in your dynamic assemblies. If you have any concerns, reply with your drawing, and we will provide a complimentary dynamic load analysis.<br><br>"
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
            return res.redirect(307, '/contact/error.html');
        }
        // 这行代码现在可以非常可靠地工作了
        const filePath = path.join(process.cwd(), current_doc.filePath);

        if (!fs.existsSync(filePath)) {
            console.error(`Attachment file not found for doc_type '${document_type}'. Path: ${filePath}`);
            return res.redirect(307, '/blog/error.html');
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
        return res.redirect(307, '/blog/casestudy-sent.html');
    } catch (error) {
        console.error('Mailer Error (Blog):', error);
        return res.redirect(307, '/blog/error.html');
    }
}