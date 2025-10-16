<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation</title>
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
        .payment-details {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .amount {
            font-size: 28px;
            font-weight: bold;
            color: #28a745;
            text-align: center;
            margin: 20px 0;
        }
        .success-icon {
            text-align: center;
            font-size: 48px;
            margin: 20px 0;
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
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
        .transaction-id {
            background: #e8f5e8;
            padding: 10px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✅ Payment Successful!</h1>
        <p>Your payment has been processed</p>
    </div>
    
    <div class="content">
        <div class="success-icon">🎉</div>
        
        <h2>Hello {{ $user->name }},</h2>
        
        <p>Thank you for your payment! Your transaction has been successfully processed and confirmed.</p>
        
        <div class="payment-details">
            <h3>Payment Details</h3>
            <div class="amount">₹{{ number_format($payment->amount, 2) }}</div>
            
            <p><strong>Payment Date:</strong> {{ $payment->payment_date->format('M d, Y \a\t h:i A') }}</p>
            <p><strong>Payment Method:</strong> {{ ucfirst($payment->payment_gateway) }}</p>
            <p><strong>Transaction ID:</strong> 
                <span class="transaction-id">{{ $payment->gateway_payment_id }}</span>
            </p>
            <p><strong>Status:</strong> <span style="color: #28a745; font-weight: bold;">Completed</span></p>
            
            @if($invoice)
            <hr style="margin: 20px 0;">
            <h4>Invoice Details</h4>
            <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
            <p><strong>Property:</strong> {{ $invoice->property->title ?? 'N/A' }}</p>
            <p><strong>Description:</strong> {{ $invoice->description }}</p>
            @endif
        </div>
        
        <div style="text-align: center;">
            <a href="{{ $dashboardUrl }}" class="button">View in Dashboard</a>
        </div>
        
        <h3>What's Next?</h3>
        <ul>
            <li>📧 You will receive a receipt via email</li>
            <li>📋 Your invoice status has been updated to "Paid"</li>
            <li>📊 Payment details are available in your dashboard</li>
            <li>💾 Keep this email as proof of payment</li>
        </ul>
        
        <h3>Need Help?</h3>
        <p>If you have any questions about this payment or need assistance, please don't hesitate to contact our support team.</p>
        
        <p>Thank you for using SaleMitra!</p>
        
        <p>Best regards,<br>
        The SaleMitra Team</p>
    </div>
    
    <div class="footer">
        <p>This payment confirmation was sent to {{ $user->email }}.</p>
        <p>&copy; {{ date('Y') }} SaleMitra. All rights reserved.</p>
    </div>
</body>
</html>
