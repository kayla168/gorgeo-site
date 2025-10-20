import formidable from 'formidable';
import fs from 'fs';
import nodemailer from 'nodemailer';

export const config = {
  api: {
    bodyParser: false, // 告诉 Vercel 不使用默认 body 解析器
  },
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
    const form = new formidable.IncomingForm();
    form.uploadDir = "/tmp"; // Node.js 支持的临时路径（Vercel 本地可用）
    form.keepExtensions = true;

    form.parse(req, async (err, fields, files) => {
      if (err) {
        console.error("Form parse error:", err);
        return res.status(500).end("Error parsing form");
      }

      const name = fields.name?.toString().trim();
      const email = fields.email?.toString().trim();
      const company = fields.company?.toString().trim();
      const message = fields.message?.toString().trim();

      if (!name || !email || !message) {
        return res.redirect(307, '/contact/error.html');
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

      const htmlBody = `
        <strong>Name:</strong> ${name}<br>
        <strong>Email:</strong> ${email}<br>
        <strong>Company:</strong> ${company || 'N/A'}<br>
        <strong>Message:</strong><br>${message.replace(/\n/g, '<br>')}
      `;

      const attachments = [];
      if (files.drawing) {
        const file = Array.isArray(files.drawing) ? files.drawing[0] : files.drawing;
        if (file && file.filepath && fs.existsSync(file.filepath)) {
          attachments.push({
            filename: file.originalFilename || 'attachment.pdf',
            path: file.filepath,
          });
        }
      }

      // 1. 发送邮件给 Catherine
      await transporter.sendMail({
        from: `"${name} (Website Inquiry)" <${process.env.FROM_EMAIL}>`,
        to: process.env.FROM_EMAIL,
        replyTo: email,
        subject: `New Technical Inquiry from ${name}` + (company ? ` (${company})` : ""),
        html: htmlBody,
        attachments,
      });

      // 2. 自动回复用户
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

      return res.redirect(307, '/contact/thank-you.html');
    });

  } catch (error) {
    console.error('Contact Form Error:', error);
    return res.redirect(307, '/contact/error.html');
  }
}
