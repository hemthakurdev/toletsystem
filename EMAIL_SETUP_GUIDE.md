# 📧 Email System Setup Guide

This guide will help you configure the email system for SaleMitra to send notifications, invoices, and other important communications.

## 🚀 Quick Setup

### 1. Configure SMTP Settings

Update your `.env` file with your email provider settings:

```env
# Email Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 2. Test Email Configuration

Run the email test command:

```bash
php artisan email:test your-email@example.com
```

## 📋 Email Providers Setup

### Gmail Setup

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-gmail@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-gmail@gmail.com"
MAIL_FROM_NAME="SaleMitra"
```

**Note:** For Gmail, you need to:
1. Enable 2-factor authentication
2. Generate an App Password
3. Use the App Password instead of your regular password

### Outlook/Hotmail Setup

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-mail.outlook.com
MAIL_PORT=587
MAIL_USERNAME=your-email@outlook.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@outlook.com"
MAIL_FROM_NAME="SaleMitra"
```

### SendGrid Setup

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="SaleMitra"
```

### Mailgun Setup

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your-mailgun-smtp-username
MAIL_PASSWORD=your-mailgun-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="SaleMitra"
```

### Amazon SES Setup

```env
MAIL_MAILER=smtp
MAIL_HOST=email-smtp.us-east-1.amazonaws.com
MAIL_PORT=587
MAIL_USERNAME=your-ses-smtp-username
MAIL_PASSWORD=your-ses-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="SaleMitra"
```

## 📧 Email Templates

SaleMitra includes the following email templates:

### 1. Welcome Email
- **Trigger:** New user registration
- **Template:** `emails.welcome`
- **Features:** Login credentials, platform overview

### 2. Invoice Email
- **Trigger:** Invoice generation
- **Template:** `emails.invoice`
- **Features:** Invoice details, payment link, PDF attachment

### 3. Lead Notification Email
- **Trigger:** New lead received
- **Template:** `emails.lead-notification`
- **Features:** Lead details, property information, contact info

### 4. Payment Confirmation Email
- **Trigger:** Successful payment
- **Template:** `emails.payment-confirmation`
- **Features:** Payment details, transaction ID, receipt

### 5. Property Approval Email
- **Trigger:** Property approved/rejected
- **Template:** `emails.property-approval`
- **Features:** Approval status, property details, next steps

## 🔧 Email Service Usage

### Sending Emails Programmatically

```php
use App\Services\EmailService;

$emailService = new EmailService();

// Send welcome email
$emailService->sendWelcomeEmail($user, $password);

// Send invoice email
$emailService->sendInvoiceEmail($invoice, $user, $pdfPath);

// Send lead notification
$emailService->sendLeadNotificationEmail($lead, $property, $propertyOwner);

// Send payment confirmation
$emailService->sendPaymentConfirmationEmail($payment, $user);

// Send property approval notification
$emailService->sendPropertyApprovalEmail($property, $user, 'approved', $reason);
```

### Queue Configuration

All emails are queued by default for better performance. Configure your queue:

```env
QUEUE_CONNECTION=database
```

Run the queue worker:

```bash
php artisan queue:work
```

## 🛠️ Troubleshooting

### Common Issues

1. **Authentication Failed**
   - Check username and password
   - For Gmail, use App Password
   - Verify 2FA is enabled for Gmail

2. **Connection Timeout**
   - Check firewall settings
   - Verify SMTP port is open
   - Try different ports (587, 465, 25)

3. **Emails Going to Spam**
   - Set up SPF, DKIM, and DMARC records
   - Use a dedicated domain for sending
   - Avoid spam trigger words

4. **SSL/TLS Errors**
   - Verify encryption setting (tls/ssl)
   - Check certificate validity
   - Try different encryption methods

### Testing Commands

```bash
# Test email configuration
php artisan email:test your-email@example.com

# Check queue status
php artisan queue:work --once

# View email logs
tail -f storage/logs/laravel.log
```

## 📊 Email Monitoring

### Log Files
- Email logs are stored in `storage/logs/laravel.log`
- Failed emails are logged with error details
- Successful sends are logged for tracking

### Statistics
The EmailService provides basic statistics:
- Total emails sent today/week/month
- Failed email count
- Most common email types

## 🔒 Security Best Practices

1. **Use Environment Variables**
   - Never hardcode email credentials
   - Use `.env` file for configuration

2. **App Passwords**
   - Use app-specific passwords for Gmail
   - Rotate passwords regularly

3. **Rate Limiting**
   - Implement email rate limiting
   - Monitor for abuse

4. **Validation**
   - Validate email addresses before sending
   - Implement unsubscribe functionality

## 📱 Mobile-Friendly Templates

All email templates are:
- ✅ Mobile responsive
- ✅ Cross-client compatible
- ✅ Accessible
- ✅ Branded with SaleMitra styling

## 🎨 Customizing Templates

Email templates are located in `resources/views/emails/`:

- `welcome.blade.php` - Welcome email
- `invoice.blade.php` - Invoice email
- `lead-notification.blade.php` - Lead notifications
- `payment-confirmation.blade.php` - Payment confirmations
- `property-approval.blade.php` - Property approval/rejection

### Template Variables

Each template receives specific variables:

```php
// Welcome email
$user, $password, $loginUrl

// Invoice email
$invoice, $user, $paymentUrl

// Lead notification
$lead, $property, $propertyOwner, $dashboardUrl

// Payment confirmation
$payment, $user, $invoice, $dashboardUrl

// Property approval
$property, $user, $status, $reason, $dashboardUrl, $marketplaceUrl
```

## 🚀 Production Deployment

### Recommended Settings

```env
# Production email settings
MAIL_MAILER=smtp
MAIL_HOST=your-production-smtp-host
MAIL_PORT=587
MAIL_USERNAME=production-email@yourdomain.com
MAIL_PASSWORD=secure-production-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="SaleMitra"

# Queue configuration
QUEUE_CONNECTION=redis
```

### Monitoring

Set up monitoring for:
- Email delivery rates
- Bounce rates
- Spam complaints
- Queue processing

## 📞 Support

If you need help with email configuration:

1. Check the Laravel Mail documentation
2. Review your email provider's SMTP settings
3. Test with the provided command: `php artisan email:test`
4. Check application logs for error details

---

**Happy Emailing! 📧✨**
