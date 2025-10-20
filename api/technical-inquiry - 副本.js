import nodemailer from 'nodemailer';

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
        const { name, email, company, message } = req.body;

        if (!name || !email || !message) {
            return res.redirect(307, '/contact/error.html');
        }

        const transporter = nodemailer.createTransport({
            host: process.env.SMTP_HOST, port: process.env.SMTP_PORT, secure: true,
            auth: { user: process.env.SMTP_USERNAME, pass: process.env.SMTP_PASSWORD },
        });

        // 1. (等同于 PHP 的第一封邮件：发送通知给管理员)
        await transporter.sendMail({
            from: `"${name} (Website Inquiry)" <${process.env.FROM_EMAIL}>`,
            to: process.env.FROM_EMAIL,
            replyTo: email,
            subject: `New Technical Inquiry from ${name}` + (company ? ` (${company})` : ""),
            html: `<strong>Name:</strong> ${name}<br><strong>Email:</strong> ${email}<br><strong>Company:</strong> ${company || 'N/A'}<br><strong>Message:</strong><br>${message.replace(/\n/g, '<br>')}`,
            // 注意: 文件上传在 Vercel Serverless Function 中需要更复杂的处理，暂时省略
        });
        
        // 2. (等同于 PHP 的第二封邮件：发送自动回复给用户)
        const autoReplyBody = `
        <div style='font-family: Calibri, sans-serif; font-size: 11pt; color: #333; line-height: 1.5;'>
            <p>Hi ${name},</p>
            <p>This is an automatic confirmation that we have successfully received your inquiry. Thank you for reaching out.</p>
            <p>Our engineering team will personally review your message and get back to you within one business day. If drawings or files are needed for the review, we will request them in our follow-up email.</p>
            <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
            <p>${signature}</p>
        </div>`;
        
        await transporter.sendMail({
            from: `"Catherine Zhang | Gorgeo Fasteners" <${process.env.FROM_EMAIL}>`,
            to: email,
            subject: "Confirmation: We've received your inquiry [Analysis in Progress]",
            html: autoReplyBody,
        });

        // 3. (等同于 PHP 的成功跳转)
        return res.redirect(307, '/contact/thank-you.html');

    } catch (error) {
        console.error('Contact Form Error:', error);
        return res.redirect(307, '/contact/error.html');
    }
}