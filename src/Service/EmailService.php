<?php
declare(strict_types=1);

namespace Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private string $smtpHost;
    private int $smtpPort;
    private string $smtpUsername;
    private string $smtpPassword;
    private string $fromEmail;
    private string $fromName;
    private bool $enabled;

    public function __construct()
    {
        // Load email configuration from environment or config
        $config = require __DIR__ . '/../../config/email.php';
        
        $this->smtpHost     = $config['smtp_host'] ?? 'smtp.gmail.com';
        $this->smtpPort     = $config['smtp_port'] ?? 587;
        $this->smtpUsername = $config['smtp_username'] ?? '';
        $this->smtpPassword = $config['smtp_password'] ?? '';
        $this->fromEmail    = $config['from_email'] ?? '';
        $this->fromName     = $config['from_name'] ?? 'Agape House Ministries';
        $this->enabled      = $config['enabled'] ?? false;
    }

    /**
     * Send a notification email to a member
     * 
     * @param string $toEmail Recipient email address
     * @param string $toName Recipient name
     * @param string $subject Email subject
     * @param string $htmlBody HTML email body
     * @param string $textBody Plain text email body (fallback)
     * @return bool Success status
     */
    public function sendNotificationEmail(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        string $textBody = ''
    ): bool {
        // Skip if email is disabled
        if (!$this->enabled) {
            return true;
        }

        // Validate email configuration
        if (empty($this->smtpUsername) || empty($this->smtpPassword) || empty($this->fromEmail)) {
            error_log('EmailService: SMTP credentials not configured');
            return false;
        }

        try {
            $mail = new PHPMailer(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host       = $this->smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->smtpUsername;
            $mail->Password   = $this->smtpPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $this->smtpPort;
            $mail->CharSet    = 'UTF-8';

            // Recipients
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($toEmail, $toName);
            $mail->addReplyTo($this->fromEmail, $this->fromName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = $textBody ?: strip_tags($htmlBody);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("EmailService: Failed to send email to {$toEmail}: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Build notification email content based on notification type
     * 
     * @param array $notification Notification data
     * @param array $actor Actor member data
     * @param string $siteUrl Base URL of the site
     * @return array ['subject' => string, 'html' => string, 'text' => string]
     */
    public function buildNotificationContent(array $notification, ?array $actor, string $siteUrl): array
    {
        $type = $notification['type'] ?? '';
        $targetType = $notification['target_type'] ?? '';
        $targetTitle = $notification['target_title'] ?? '';
        $actorName = $actor ? ($actor['full_name'] ?? 'A member') : 'Agape House Ministries';
        $actorId = $notification['actor_id'] ?? 0;
        
        // Build target URL
        $targetUrl = $this->buildTargetUrl($siteUrl, $targetType, $notification['target_id'] ?? 0);
        
        // Build content based on notification type
        switch ($type) {
            case 'like':
                $subject = "{$actorName} liked your post";
                $message = "{$actorName} liked your post: {$targetTitle}";
                break;
            
            case 'comment':
                $subject = "{$actorName} commented on your post";
                $message = "{$actorName} commented on your post: {$targetTitle}";
                break;
            
            case 'share':
                $subject = "{$actorName} shared your post";
                $message = "{$actorName} shared your post: {$targetTitle}";
                break;
            
            case 'comment_like':
                $subject = "{$actorName} liked your comment";
                $message = "{$actorName} liked your comment";
                break;
            
            case 'comment_reply':
                $subject = "{$actorName} replied to your comment";
                $message = "{$actorName} replied to your comment";
                break;
            
            case 'follow':
                $subject = "{$actorName} started following you";
                $message = "{$actorName} is now following you";
                $targetUrl = "{$siteUrl}/?page=profile&id={$actorId}";
                break;
            
            case 'follow_back':
                $subject = "{$actorName} followed you back";
                $message = "{$actorName} followed you back";
                $targetUrl = "{$siteUrl}/?page=profile&id={$actorId}";
                break;
            
            case 'new_event':
                $subject = "New Event: {$targetTitle}";
                $message = "A new event has been posted: {$targetTitle}";
                break;
            
            case 'new_announcement':
                $subject = "New Announcement: {$targetTitle}";
                $message = "A new announcement has been posted: {$targetTitle}";
                break;
            
            case 'contact_reply':
                $subject = "Response to your message: {$targetTitle}";
                $message = "You have received a response to your message: {$targetTitle}";
                break;
            
            case 'gallery_approved':
                $subject = "Your gallery submission was approved";
                $message = "Your gallery submission has been approved and is now visible to all members";
                break;
            
            case 'gallery_rejected':
                $subject = "Your gallery submission needs attention";
                $message = "Your gallery submission could not be approved. Please check the details and try again";
                break;
            
            default:
                $subject = "New notification from Agape House Ministries";
                $message = "You have a new notification";
        }
        
        // Build HTML email
        $html = $this->buildHtmlTemplate($subject, $message, $targetUrl, $actorName);
        
        // Build plain text email
        $text = $this->buildTextTemplate($message, $targetUrl);
        
        return [
            'subject' => $subject,
            'html' => $html,
            'text' => $text
        ];
    }

    /**
     * Build target URL based on target type and ID
     */
    private function buildTargetUrl(string $siteUrl, string $targetType, int $targetId): string
    {
        switch ($targetType) {
            case 'article':
                return "{$siteUrl}/?page=read&id={$targetId}";
            case 'media':
                return "{$siteUrl}/?page=watch";
            case 'announcement':
                return "{$siteUrl}/?page=announcement";
            case 'event':
                return "{$siteUrl}/?page=events";
            case 'gallery':
                return "{$siteUrl}/?page=gallery";
            case 'contact_message':
                return "{$siteUrl}/?page=contact";
            default:
                return $siteUrl;
        }
    }

    /**
     * Build HTML email template
     */
    private function buildHtmlTemplate(string $subject, string $message, string $actionUrl, string $actorName): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$subject}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .content { padding: 30px 20px; }
        .message { font-size: 16px; margin-bottom: 20px; color: #555; }
        .button-container { text-align: center; margin: 30px 0; }
        .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: 600; }
        .button:hover { background: #5568d3; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #e9ecef; }
        .footer a { color: #667eea; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 Agape House Ministries</h1>
        </div>
        <div class="content">
            <p class="message"><strong>{$message}</strong></p>
            <div class="button-container">
                <a href="{$actionUrl}" class="button">View Now</a>
            </div>
        </div>
        <div class="footer">
            <p>This notification was sent to you as a member of Agape House Ministries</p>
            <p>Visit us at <a href="{$actionUrl}">Agape House Ministries</a></p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Build plain text email template
     */
    private function buildTextTemplate(string $message, string $actionUrl): string
    {
        return <<<TEXT
Agape House Ministries Notification

{$message}

View now: {$actionUrl}

---
This notification was sent to you as a member of Agape House Ministries.
TEXT;
    }
}
