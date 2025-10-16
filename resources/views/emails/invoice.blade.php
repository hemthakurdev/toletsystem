<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
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
        .invoice-details {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
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
        .due-date {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Invoice #{{ $invoice->invoice_number }}</h1>
        <p>SaleMitra Property Management</p>
    </div>
    
    <div class="content">
        <h2>Hello {{ $user->name }},</h2>
        
        <p>You have received a new invoice from SaleMitra. Please find the details below:</p>
        
        <div class="invoice-details">
            <h3>Invoice Details</h3>
            <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
            <p><strong>Issue Date:</strong> {{ $invoice->issue_date->format('M d, Y') }}</p>
            <p><strong>Due Date:</strong> <span class="due-date">{{ $invoice->due_date->format('M d, Y') }}</span></p>
            <p><strong>Property:</strong> {{ $invoice->property->title ?? 'N/A' }}</p>
            <p><strong>Tenant:</strong> {{ $invoice->tenant->name ?? 'N/A' }}</p>
            <p><strong>Description:</strong> {{ $invoice->description }}</p>
            <p><strong>Amount:</strong> <span class="amount">₹{{ number_format($invoice->amount, 2) }}</span></p>
            <p><strong>Status:</strong> {{ ucfirst($invoice->status) }}</p>
        </div>
        
        @if($invoice->status === 'pending')
        <div style="text-align: center;">
            <a href="{{ $paymentUrl }}" class="button">Pay Now</a>
        </div>
        
        <p><strong>Payment Methods:</strong></p>
        <ul>
            <li>💳 Credit/Debit Cards</li>
            <li>🏦 Net Banking</li>
            <li>📱 UPI</li>
            <li>💰 Wallet</li>
        </ul>
        @endif
        
        <h3>Important Notes:</h3>
        <ul>
            <li>Please pay this invoice by the due date to avoid late fees</li>
            <li>You can download the PDF invoice from your dashboard</li>
            <li>For any questions about this invoice, please contact us</li>
        </ul>
        
        <p>Thank you for using SaleMitra!</p>
        
        <p>Best regards,<br>
        The SaleMitra Team</p>
    </div>
    
    <div class="footer">
        <p>This invoice was sent to {{ $user->email }}. Please keep this email for your records.</p>
        <p>&copy; {{ date('Y') }} SaleMitra. All rights reserved.</p>
    </div>
</body>
</html>
