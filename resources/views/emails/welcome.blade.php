<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to SaleMitra</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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
            background: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .credentials {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Welcome to SaleMitra!</h1>
        <p>Your rental management platform is ready</p>
    </div>
    
    <div class="content">
        <h2>Hello {{ $user->name }},</h2>
        
        <p>Welcome to SaleMitra! We're excited to have you on board. Your account has been successfully created and you can now start managing your properties and tenants.</p>
        
        @if($password)
        <div class="credentials">
            <h3>Your Login Credentials:</h3>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Password:</strong> {{ $password }}</p>
            <p><em>Please change your password after your first login for security.</em></p>
        </div>
        @endif
        
        <h3>What you can do with SaleMitra:</h3>
        <ul>
            <li>📋 Manage your properties and listings</li>
            <li>👥 Track tenants and lease agreements</li>
            <li>💰 Generate and send invoices</li>
            <li>💳 Process payments securely</li>
            <li>📊 View analytics and reports</li>
            <li>📱 Access from any device</li>
        </ul>
        
        <div style="text-align: center;">
            <a href="{{ $loginUrl }}" class="button">Login to Your Dashboard</a>
        </div>
        
        <h3>Need Help?</h3>
        <p>If you have any questions or need assistance getting started, please don't hesitate to reach out to our support team.</p>
        
        <p>Best regards,<br>
        The SaleMitra Team</p>
    </div>
    
    <div class="footer">
        <p>This email was sent to {{ $user->email }}. If you didn't create an account, please ignore this email.</p>
        <p>&copy; {{ date('Y') }} SaleMitra. All rights reserved.</p>
    </div>
</body>
</html>
