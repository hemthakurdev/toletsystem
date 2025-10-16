<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Lead - {{ $property->title }}</title>
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
        .lead-details {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .property-details {
            background: #e8f5e8;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background: #28a745;
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
        .priority {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .priority.high {
            background: #dc3545;
            color: white;
        }
        .priority.medium {
            background: #ffc107;
            color: #000;
        }
        .priority.low {
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎉 New Lead Received!</h1>
        <p>Someone is interested in your property</p>
    </div>
    
    <div class="content">
        <h2>Hello {{ $propertyOwner->name }},</h2>
        
        <p>Great news! You have received a new lead for your property. Here are the details:</p>
        
        <div class="lead-details">
            <h3>Lead Information</h3>
            <p><strong>Name:</strong> {{ $lead->name }}</p>
            <p><strong>Email:</strong> {{ $lead->email }}</p>
            <p><strong>Phone:</strong> {{ $lead->phone ?? 'Not provided' }}</p>
            <p><strong>Message:</strong> {{ $lead->message ?? 'No message provided' }}</p>
            <p><strong>Priority:</strong> 
                <span class="priority {{ $lead->priority ?? 'medium' }}">
                    {{ ucfirst($lead->priority ?? 'medium') }}
                </span>
            </p>
            <p><strong>Received:</strong> {{ $lead->created_at->format('M d, Y \a\t h:i A') }}</p>
        </div>
        
        <div class="property-details">
            <h3>Property Details</h3>
            <p><strong>Title:</strong> {{ $property->title }}</p>
            <p><strong>Type:</strong> {{ ucfirst($property->property_type) }}</p>
            <p><strong>Location:</strong> {{ $property->city }}, {{ $property->state }}</p>
            <p><strong>Rent:</strong> ₹{{ number_format($property->rent_amount) }}/month</p>
        </div>
        
        <div style="text-align: center;">
            <a href="{{ $dashboardUrl }}" class="button">View Lead in Dashboard</a>
        </div>
        
        <h3>Next Steps:</h3>
        <ul>
            <li>📞 Contact the lead as soon as possible</li>
            <li>📋 Schedule a property viewing if interested</li>
            <li>📝 Update the lead status in your dashboard</li>
            <li>💬 Keep track of all communications</li>
        </ul>
        
        <h3>Tips for Converting Leads:</h3>
        <ul>
            <li>Respond quickly - first response gets the best results</li>
            <li>Be professional and helpful</li>
            <li>Provide additional property details if requested</li>
            <li>Follow up if you don't hear back</li>
        </ul>
        
        <p>Good luck with your new lead!</p>
        
        <p>Best regards,<br>
        The SaleMitra Team</p>
    </div>
    
    <div class="footer">
        <p>This notification was sent to {{ $propertyOwner->email }}.</p>
        <p>&copy; {{ date('Y') }} SaleMitra. All rights reserved.</p>
    </div>
</body>
</html>
