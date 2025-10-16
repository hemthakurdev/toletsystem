<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices Export - {{ $organization->name ?? 'SaleMitra' }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #667eea;
            margin: 0;
            font-size: 24px;
        }
        
        .header p {
            margin: 5px 0;
            color: #666;
        }
        
        .export-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .export-info h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .export-info p {
            margin: 5px 0;
            color: #666;
        }
        
        .filters-applied {
            background: #e3f2fd;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .filters-applied h4 {
            margin: 0 0 5px 0;
            color: #1976d2;
        }
        
        .filters-applied ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .filters-applied li {
            margin: 2px 0;
            color: #666;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #667eea;
            color: white;
            font-weight: bold;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .status {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status.pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status.paid {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status.overdue {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .status.cancelled {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .amount {
            text-align: right;
            font-weight: bold;
        }
        
        .summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .summary h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        
        .summary-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 14px;
            color: #667eea;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        
        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 40px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Invoices Export Report</h1>
        <p><strong>{{ $organization->name ?? 'SaleMitra Property Management' }}</strong></p>
        <p>{{ $organization->email ?? 'support@salemitra.com' }}</p>
    </div>
    
    <div class="export-info">
        <h3>📋 Export Information</h3>
        <p><strong>Generated On:</strong> {{ $export_date->format('F j, Y \a\t g:i A') }}</p>
        <p><strong>Total Records:</strong> {{ $invoices->count() }}</p>
        @if($date_range['from'] || $date_range['to'])
            <p><strong>Date Range:</strong> 
                {{ $date_range['from'] ? \Carbon\Carbon::parse($date_range['from'])->format('M j, Y') : 'Start' }} 
                to 
                {{ $date_range['to'] ? \Carbon\Carbon::parse($date_range['to'])->format('M j, Y') : 'End' }}
            </p>
        @endif
    </div>
    
    @if(!empty($filters))
        <div class="filters-applied">
            <h4>🔍 Filters Applied</h4>
            <ul>
                @if(isset($filters['status']))
                    <li><strong>Status:</strong> {{ is_array($filters['status']) ? implode(', ', $filters['status']) : $filters['status'] }}</li>
                @endif
                @if(isset($filters['tenant_id']))
                    <li><strong>Tenant ID:</strong> {{ is_array($filters['tenant_id']) ? implode(', ', $filters['tenant_id']) : $filters['tenant_id'] }}</li>
                @endif
                @if(isset($filters['property_id']))
                    <li><strong>Property ID:</strong> {{ is_array($filters['property_id']) ? implode(', ', $filters['property_id']) : $filters['property_id'] }}</li>
                @endif
                @if(isset($filters['amount_min']))
                    <li><strong>Minimum Amount:</strong> ₹{{ number_format($filters['amount_min'], 2) }}</li>
                @endif
                @if(isset($filters['amount_max']))
                    <li><strong>Maximum Amount:</strong> ₹{{ number_format($filters['amount_max'], 2) }}</li>
                @endif
            </ul>
        </div>
    @endif
    
    @if($invoices->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Tenant</th>
                    <th>Property</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->tenant->name ?? 'N/A' }}</td>
                        <td>{{ $invoice->property->title ?? 'N/A' }}</td>
                        <td class="amount">₹{{ number_format($invoice->amount, 2) }}</td>
                        <td>
                            <span class="status {{ $invoice->status }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td>{{ $invoice->due_date->format('M j, Y') }}</td>
                        <td>{{ $invoice->created_at->format('M j, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="summary">
            <h3>📈 Summary</h3>
            <div class="summary-row">
                <span>Total Invoices:</span>
                <span>{{ $invoices->count() }}</span>
            </div>
            <div class="summary-row">
                <span>Total Amount:</span>
                <span>₹{{ number_format($invoices->sum('amount'), 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Pending Invoices:</span>
                <span>{{ $invoices->where('status', 'pending')->count() }}</span>
            </div>
            <div class="summary-row">
                <span>Paid Invoices:</span>
                <span>{{ $invoices->where('status', 'paid')->count() }}</span>
            </div>
            <div class="summary-row">
                <span>Overdue Invoices:</span>
                <span>{{ $invoices->where('status', 'overdue')->count() }}</span>
            </div>
            <div class="summary-row">
                <span>Pending Amount:</span>
                <span>₹{{ number_format($invoices->where('status', 'pending')->sum('amount'), 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Paid Amount:</span>
                <span>₹{{ number_format($invoices->where('status', 'paid')->sum('amount'), 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Overdue Amount:</span>
                <span>₹{{ number_format($invoices->where('status', 'overdue')->sum('amount'), 2) }}</span>
            </div>
        </div>
    @else
        <div class="no-data">
            <h3>📭 No Invoices Found</h3>
            <p>No invoices match the specified criteria.</p>
        </div>
    @endif
    
    <div class="footer">
        <p>Generated by SaleMitra Property Management System</p>
        <p>This report was automatically generated on {{ $export_date->format('F j, Y \a\t g:i A') }}</p>
    </div>
</body>
</html>
