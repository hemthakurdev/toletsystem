<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property {{ ucfirst($status) }} - {{ $property->title }}</title>
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
            background: linear-gradient(135deg, {{ $status === 'approved' ? '#28a745 0%, #20c997 100%' : '#dc3545 0%, #fd7e14 100%' }});
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
        .property-details {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid {{ $status === 'approved' ? '#28a745' : '#dc3545' }};
        }
        .status-icon {
            text-align: center;
            font-size: 48px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background: {{ $status === 'approved' ? '#28a745' : '#667eea' }};
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
        .reason-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>
            @if($status === 'approved')
                ✅ Property Approved!
            @else
                ❌ Property Rejected
            @endif
        </h1>
        <p>{{ $property->title }}</p>
    </div>
    
    <div class="content">
        <div class="status-icon">
            @if($status === 'approved')
                🎉
            @else
                😔
            @endif
        </div>
        
        <h2>Hello {{ $user->name }},</h2>
        
        @if($status === 'approved')
            <p>Great news! Your property listing has been approved and is now live on our marketplace.</p>
        @else
            <p>We regret to inform you that your property listing has been rejected. Please see the reason below.</p>
        @endif
        
        <div class="property-details">
            <h3>Property Details</h3>
            <p><strong>Title:</strong> {{ $property->title }}</p>
            <p><strong>Type:</strong> {{ ucfirst($property->property_type) }}</p>
            <p><strong>Location:</strong> {{ $property->city }}, {{ $property->state }}</p>
            <p><strong>Rent:</strong> ₹{{ number_format($property->rent_amount) }}/month</p>
            <p><strong>Status:</strong> 
                <span style="color: {{ $status === 'approved' ? '#28a745' : '#dc3545' }}; font-weight: bold;">
                    {{ ucfirst($status) }}
                </span>
            </p>
        </div>
        
        @if($status === 'rejected' && $reason)
        <div class="reason-box">
            <h4>Reason for Rejection:</h4>
            <p>{{ $reason }}</p>
        </div>
        @endif
        
        <div style="text-align: center;">
            @if($status === 'approved')
                <a href="{{ $marketplaceUrl }}" class="button">View Live Listing</a>
            @else
                <a href="{{ $dashboardUrl }}" class="button">Edit Property</a>
            @endif
        </div>
        
        @if($status === 'approved')
        <h3>What's Next?</h3>
        <ul>
            <li>🏠 Your property is now visible to potential tenants</li>
            <li>📧 You'll receive notifications for new leads</li>
            <li>📊 Track views and inquiries in your dashboard</li>
            <li>✏️ You can edit your listing anytime</li>
        </ul>
        
        <h3>Tips for Success:</h3>
        <ul>
            <li>Keep your listing updated with current information</li>
            <li>Respond quickly to tenant inquiries</li>
            <li>Add high-quality photos to attract more interest</li>
            <li>Set competitive pricing based on market rates</li>
        </ul>
        @else
        <h3>Next Steps:</h3>
        <ul>
            <li>📝 Review the rejection reason carefully</li>
            <li>✏️ Make necessary changes to your property listing</li>
            <li>🔄 Resubmit your property for approval</li>
            <li>📞 Contact support if you need clarification</li>
        </ul>
        
        <h3>Common Issues to Check:</h3>
        <ul>
            <li>Complete and accurate property information</li>
            <li>High-quality photos of the property</li>
            <li>Reasonable and competitive pricing</li>
            <li>Proper contact information</li>
        </ul>
        @endif
        
        <p>Thank you for using SaleMitra!</p>
        
        <p>Best regards,<br>
        The SaleMitra Team</p>
    </div>
    
    <div class="footer">
        <p>This notification was sent to {{ $user->email }}.</p>
        <p>&copy; {{ date('Y') }} SaleMitra. All rights reserved.</p>
    </div>
</body>
</html>
