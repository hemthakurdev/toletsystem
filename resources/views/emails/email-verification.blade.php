<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - SaleMitra</title>
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
            background: #28a745;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background: #218838;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .features {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .feature-item {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }
        .feature-icon {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✅ Welcome to SaleMitra!</h1>
        <p>Please verify your email address to get started</p>
    </div>
    
    <div class="content">
        <h2>Hello {{ $user->name }},</h2>
        
        <p>Thank you for registering with SaleMitra! To complete your registration and start using our property management platform, please verify your email address by clicking the button below:</p>
        
        <div style="text-align: center;">
            <a href="{{ $verificationUrl }}" class="button">Verify My Email Address</a>
        </div>
        
        <div class="info">
            <strong>📧 Email Verification Required:</strong> You must verify your email address before you can access all features of your SaleMitra account.
        </div>
        
        <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
        <p style="word-break: break-all; background: #e9ecef; padding: 10px; border-radius: 5px; font-family: monospace;">
            {{ $verificationUrl }}
        </p>
        
        <div class="features">
            <h3>🚀 What you can do after verification:</h3>
            <div class="feature-item">
                <span class="feature-icon">🏠</span>
                <span>Manage your properties and listings</span>
            </div>
            <div class="feature-item">
                <span class="feature-icon">👥</span>
                <span>Track tenants and their information</span>
            </div>
            <div class="feature-item">
                <span class="feature-icon">💰</span>
                <span>Generate and manage invoices</span>
            </div>
            <div class="feature-item">
                <span class="feature-icon">📊</span>
                <span>View analytics and reports</span>
            </div>
            <div class="feature-item">
                <span class="feature-icon">📱</span>
                <span>Access mobile-friendly dashboard</span>
            </div>
        </div>
        
        <p><strong>Need help?</strong> If you're having trouble verifying your email or have any questions, please don't hesitate to contact our support team.</p>
        
        <p>We're excited to help you streamline your property management!</p>
    </div>
    
    <div class="footer">
        <p>This email was sent from SaleMitra Property Management System.</p>
        <p>If you didn't create an account, you can safely ignore this email.</p>
        <p>&copy; {{ date('Y') }} SaleMitra. All rights reserved.</p>
    </div>
</body>
</html>
