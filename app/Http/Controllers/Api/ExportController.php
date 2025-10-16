<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Document;
use App\Models\Lead;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ExportController extends Controller
{
    /**
     * Export properties to Excel
     */
    public function exportPropertiesToExcel(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'filters' => 'array',
            'filters.status' => 'in:active,inactive,pending',
            'filters.property_type' => 'string',
            'filters.category' => 'in:rent,sale',
            'filters.city' => 'string',
            'date_from' => 'date',
            'date_to' => 'date|after_or_equal:date_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $query = Property::where('org_id', auth()->user()->org_id);

            // Apply filters
            if ($request->has('filters')) {
                $filters = $request->filters;
                
                if (isset($filters['status'])) {
                    $query->where('status', $filters['status']);
                }
                
                if (isset($filters['property_type'])) {
                    $query->where('property_type', $filters['property_type']);
                }
                
                if (isset($filters['category'])) {
                    $query->where('category', $filters['category']);
                }
                
                if (isset($filters['city'])) {
                    $query->where('city', 'like', '%' . $filters['city'] . '%');
                }
            }

            // Apply date range
            if ($request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $properties = $query->with(['tenants', 'invoices'])->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set headers
            $headers = [
                'A1' => 'ID',
                'B1' => 'Title',
                'C1' => 'Property Type',
                'D1' => 'Category',
                'E1' => 'Price',
                'F1' => 'Bedrooms',
                'G1' => 'Bathrooms',
                'H1' => 'Area (Sq Ft)',
                'I1' => 'Address',
                'J1' => 'City',
                'K1' => 'State',
                'L1' => 'Pincode',
                'M1' => 'Status',
                'N1' => 'Published',
                'O1' => 'Tenants Count',
                'P1' => 'Invoices Count',
                'Q1' => 'Created At',
            ];

            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            // Add data
            $row = 2;
            foreach ($properties as $property) {
                $sheet->setCellValue('A' . $row, $property->id);
                $sheet->setCellValue('B' . $row, $property->title);
                $sheet->setCellValue('C' . $row, $property->property_type);
                $sheet->setCellValue('D' . $row, $property->category);
                $sheet->setCellValue('E' . $row, $property->price);
                $sheet->setCellValue('F' . $row, $property->bedrooms);
                $sheet->setCellValue('G' . $row, $property->bathrooms);
                $sheet->setCellValue('H' . $row, $property->area_sqft);
                $sheet->setCellValue('I' . $row, $property->address);
                $sheet->setCellValue('J' . $row, $property->city);
                $sheet->setCellValue('K' . $row, $property->state);
                $sheet->setCellValue('L' . $row, $property->pincode);
                $sheet->setCellValue('M' . $row, $property->status);
                $sheet->setCellValue('N' . $row, $property->published ? 'Yes' : 'No');
                $sheet->setCellValue('O' . $row, $property->tenants->count());
                $sheet->setCellValue('P' . $row, $property->invoices->count());
                $sheet->setCellValue('Q' . $row, $property->created_at->format('Y-m-d H:i:s'));
                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'Q') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            $filename = 'properties_export_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
            $filepath = storage_path('app/exports/' . $filename);
            
            // Ensure directory exists
            if (!file_exists(dirname($filepath))) {
                mkdir(dirname($filepath), 0755, true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($filepath);

            return response()->json([
                'success' => true,
                'message' => 'Properties exported successfully',
                'data' => [
                    'filename' => $filename,
                    'download_url' => url('api/v1/exports/download/' . $filename),
                    'total_records' => $properties->count(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export properties: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export tenants to Excel
     */
    public function exportTenantsToExcel(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'filters' => 'array',
            'filters.status' => 'in:active,inactive',
            'filters.property_id' => 'integer|exists:properties,id',
            'date_from' => 'date',
            'date_to' => 'date|after_or_equal:date_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $query = Tenant::where('org_id', auth()->user()->org_id)
                ->with(['property', 'invoices']);

            // Apply filters
            if ($request->has('filters')) {
                $filters = $request->filters;
                
                if (isset($filters['status'])) {
                    $query->where('status', $filters['status']);
                }
                
                if (isset($filters['property_id'])) {
                    $query->where('property_id', $filters['property_id']);
                }
            }

            // Apply date range
            if ($request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $tenants = $query->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set headers
            $headers = [
                'A1' => 'ID',
                'B1' => 'Name',
                'C1' => 'Email',
                'D1' => 'Phone',
                'D1' => 'Property',
                'E1' => 'Rent Amount',
                'F1' => 'Status',
                'G1' => 'Move In Date',
                'H1' => 'Move Out Date',
                'I1' => 'Invoices Count',
                'J1' => 'Total Paid',
                'K1' => 'Created At',
            ];

            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            // Add data
            $row = 2;
            foreach ($tenants as $tenant) {
                $sheet->setCellValue('A' . $row, $tenant->id);
                $sheet->setCellValue('B' . $row, $tenant->name);
                $sheet->setCellValue('C' . $row, $tenant->email);
                $sheet->setCellValue('D' . $row, $tenant->phone);
                $sheet->setCellValue('E' . $row, $tenant->property->title ?? 'N/A');
                $sheet->setCellValue('F' . $row, $tenant->rent_amount);
                $sheet->setCellValue('G' . $row, $tenant->status);
                $sheet->setCellValue('H' . $row, $tenant->move_in_date ? $tenant->move_in_date->format('Y-m-d') : 'N/A');
                $sheet->setCellValue('I' . $row, $tenant->move_out_date ? $tenant->move_out_date->format('Y-m-d') : 'N/A');
                $sheet->setCellValue('J' . $row, $tenant->invoices->count());
                $sheet->setCellValue('K' . $row, $tenant->invoices->where('status', 'paid')->sum('amount'));
                $sheet->setCellValue('L' . $row, $tenant->created_at->format('Y-m-d H:i:s'));
                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'L') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            $filename = 'tenants_export_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
            $filepath = storage_path('app/exports/' . $filename);
            
            if (!file_exists(dirname($filepath))) {
                mkdir(dirname($filepath), 0755, true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($filepath);

            return response()->json([
                'success' => true,
                'message' => 'Tenants exported successfully',
                'data' => [
                    'filename' => $filename,
                    'download_url' => url('api/v1/exports/download/' . $filename),
                    'total_records' => $tenants->count(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export tenants: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export invoices to PDF
     */
    public function exportInvoicesToPdf(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'filters' => 'array',
            'filters.status' => 'in:pending,paid,overdue',
            'filters.tenant_id' => 'integer|exists:tenants,id',
            'date_from' => 'date',
            'date_to' => 'date|after_or_equal:date_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $query = Invoice::where('org_id', auth()->user()->org_id)
                ->with(['tenant', 'property']);

            // Apply filters
            if ($request->has('filters')) {
                $filters = $request->filters;
                
                if (isset($filters['status'])) {
                    $query->where('status', $filters['status']);
                }
                
                if (isset($filters['tenant_id'])) {
                    $query->where('tenant_id', $filters['tenant_id']);
                }
            }

            // Apply date range
            if ($request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $invoices = $query->get();

            $data = [
                'invoices' => $invoices,
                'organization' => auth()->user()->organization,
                'export_date' => now(),
                'filters' => $request->filters ?? [],
                'date_range' => [
                    'from' => $request->date_from,
                    'to' => $request->date_to,
                ],
            ];

            $pdf = Pdf::loadView('exports.invoices-pdf', $data);
            $pdf->setPaper('A4', 'landscape');

            $filename = 'invoices_export_' . now()->format('Y_m_d_H_i_s') . '.pdf';
            $filepath = storage_path('app/exports/' . $filename);
            
            if (!file_exists(dirname($filepath))) {
                mkdir(dirname($filepath), 0755, true);
            }

            $pdf->save($filepath);

            return response()->json([
                'success' => true,
                'message' => 'Invoices exported successfully',
                'data' => [
                    'filename' => $filename,
                    'download_url' => url('api/v1/exports/download/' . $filename),
                    'total_records' => $invoices->count(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export invoices: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export financial report to Excel
     */
    public function exportFinancialReportToExcel(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'include_breakdown' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $orgId = auth()->user()->org_id;
            $dateFrom = Carbon::parse($request->date_from);
            $dateTo = Carbon::parse($request->date_to);

            // Get financial data
            $invoices = Invoice::where('org_id', $orgId)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->get();

            $payments = Payment::where('org_id', $orgId)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->get();

            $expenses = Expense::where('org_id', $orgId)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->get();

            $spreadsheet = new Spreadsheet();
            
            // Summary Sheet
            $summarySheet = $spreadsheet->getActiveSheet();
            $summarySheet->setTitle('Financial Summary');
            
            $summaryData = [
                ['Metric', 'Amount'],
                ['Total Invoices Generated', $invoices->count()],
                ['Total Invoice Amount', $invoices->sum('amount')],
                ['Total Payments Received', $payments->where('status', 'completed')->sum('amount')],
                ['Total Expenses', $expenses->where('status', 'approved')->sum('amount')],
                ['Net Revenue', $payments->where('status', 'completed')->sum('amount') - $expenses->where('status', 'approved')->sum('amount')],
                ['Pending Invoices', $invoices->where('status', 'pending')->count()],
                ['Overdue Invoices', $invoices->where('status', 'overdue')->count()],
            ];

            $row = 1;
            foreach ($summaryData as $data) {
                $summarySheet->setCellValue('A' . $row, $data[0]);
                $summarySheet->setCellValue('B' . $row, $data[1]);
                $row++;
            }

            // Invoices Sheet
            if ($request->include_breakdown) {
                $invoicesSheet = $spreadsheet->createSheet();
                $invoicesSheet->setTitle('Invoices');
                
                $headers = ['ID', 'Invoice Number', 'Tenant', 'Property', 'Amount', 'Status', 'Due Date', 'Created At'];
                $col = 'A';
                foreach ($headers as $header) {
                    $invoicesSheet->setCellValue($col . '1', $header);
                    $col++;
                }

                $row = 2;
                foreach ($invoices as $invoice) {
                    $invoicesSheet->setCellValue('A' . $row, $invoice->id);
                    $invoicesSheet->setCellValue('B' . $row, $invoice->invoice_number);
                    $invoicesSheet->setCellValue('C' . $row, $invoice->tenant->name ?? 'N/A');
                    $invoicesSheet->setCellValue('D' . $row, $invoice->property->title ?? 'N/A');
                    $invoicesSheet->setCellValue('E' . $row, $invoice->amount);
                    $invoicesSheet->setCellValue('F' . $row, $invoice->status);
                    $invoicesSheet->setCellValue('G' . $row, $invoice->due_date->format('Y-m-d'));
                    $invoicesSheet->setCellValue('H' . $row, $invoice->created_at->format('Y-m-d H:i:s'));
                    $row++;
                }
            }

            $filename = 'financial_report_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
            $filepath = storage_path('app/exports/' . $filename);
            
            if (!file_exists(dirname($filepath))) {
                mkdir(dirname($filepath), 0755, true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($filepath);

            return response()->json([
                'success' => true,
                'message' => 'Financial report exported successfully',
                'data' => [
                    'filename' => $filename,
                    'download_url' => url('api/v1/exports/download/' . $filename),
                    'date_range' => [
                        'from' => $dateFrom->format('Y-m-d'),
                        'to' => $dateTo->format('Y-m-d'),
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export financial report: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download exported file
     */
    public function downloadExport(Request $request, string $filename): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filepath = storage_path('app/exports/' . $filename);
        
        if (!file_exists($filepath)) {
            abort(404, 'File not found');
        }

        return response()->download($filepath, $filename);
    }

    /**
     * Get export history
     */
    public function getExportHistory(): JsonResponse
    {
        try {
            $exportsDir = storage_path('app/exports');
            $files = [];
            
            if (is_dir($exportsDir)) {
                $fileList = scandir($exportsDir);
                foreach ($fileList as $file) {
                    if ($file !== '.' && $file !== '..') {
                        $filepath = $exportsDir . '/' . $file;
                        $files[] = [
                            'filename' => $file,
                            'size' => filesize($filepath),
                            'created_at' => date('Y-m-d H:i:s', filemtime($filepath)),
                            'download_url' => url('api/v1/exports/download/' . $file),
                        ];
                    }
                }
            }

            // Sort by creation date (newest first)
            usort($files, function ($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });

            return response()->json([
                'success' => true,
                'data' => $files,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get export history: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clean up old export files
     */
    public function cleanupOldExports(): JsonResponse
    {
        try {
            $exportsDir = storage_path('app/exports');
            $deletedCount = 0;
            
            if (is_dir($exportsDir)) {
                $fileList = scandir($exportsDir);
                foreach ($fileList as $file) {
                    if ($file !== '.' && $file !== '..') {
                        $filepath = $exportsDir . '/' . $file;
                        $fileAge = time() - filemtime($filepath);
                        
                        // Delete files older than 7 days
                        if ($fileAge > (7 * 24 * 60 * 60)) {
                            unlink($filepath);
                            $deletedCount++;
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Cleaned up {$deletedCount} old export files",
                'data' => ['deleted_count' => $deletedCount],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cleanup exports: ' . $e->getMessage(),
            ], 500);
        }
    }
}