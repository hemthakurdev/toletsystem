<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - SaleMitra</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .button {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background: #0056b3;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔐 Password Reset Request</h1>
        <p>SaleMitra Property Management</p>
    </div>
    
    <div class="content">
        <h2>Hello {{ $user->name }},</h2>
        
        <p>We received a request to reset your password for your SaleMitra account. If you made this request, click the button below to reset your password:</p>
        
        <div style="text-align: center;">
            <a href="{{ $resetUrl }}" class="button">Reset My Password</a>
        </div>
        
        <div class="warning">
            <strong>⚠️ Important:</strong> This link will expire in {{ $expiresIn }}. If you don't reset your password within this time, you'll need to request a new reset link.
        </div>
        
        <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
        <p style="word-break: break-all; background: #e9ecef; padding: 10px; border-radius: 5px; font-family: monospace;">
            {{ $resetUrl }}
        </p>
        
        <p><strong>If you didn't request this password reset:</strong></p>
        <ul>
            <li>You can safely ignore this email</li>
            <li>Your password will remain unchanged</li>
            <li>No further action is required</li>
        </ul>
        
        <p>For security reasons, we recommend:</p>
        <ul>
            <li>Using a strong, unique password</li>
            <li>Not sharing your password with anyone</li>
            <li>Logging out from shared devices</li>
        </ul>
    </div>
    
    <div class="footer">
        <p>This email was sent from SaleMitra Property Management System.</p>
        <p>If you have any questions, please contact our support team.</p>
        <p>&copy; {{ date('Y') }} SaleMitra. All rights reserved.</p>
    </div>
</body>
</html>
