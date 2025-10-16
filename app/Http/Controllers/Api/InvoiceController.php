<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\Property;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }
    /**
     * Display a listing of invoices
     */
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::with(['organization', 'tenant', 'property', 'payments'])
            ->where('org_id', auth()->user()->org_id);

        // Apply filters
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('tenant', function ($tenantQuery) use ($search) {
                      $tenantQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('due_date_from') && $request->has('due_date_to')) {
            $query->whereBetween('due_date', [$request->due_date_from, $request->due_date_to]);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $invoices = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    /**
     * Store a newly created invoice
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required|exists:tenants,id',
            'property_id' => 'required|exists:properties,id',
            'type' => 'required|in:rent,maintenance,penalty,other',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date|after_or_equal:today',
            'description' => 'nullable|string|max:1000',
            'items' => 'nullable|array',
            'items.*.description' => 'required_with:items|string|max:255',
            'items.*.quantity' => 'required_with:items|numeric|min:0',
            'items.*.rate' => 'required_with:items|numeric|min:0',
            'items.*.amount' => 'required_with:items|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if tenant and property belong to user's organization
        $tenant = Tenant::where('id', $request->tenant_id)
            ->where('org_id', auth()->user()->org_id)
            ->first();

        $property = Property::where('id', $request->property_id)
            ->where('org_id', auth()->user()->org_id)
            ->first();

        if (!$tenant || !$property) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant or property not found or unauthorized',
            ], 404);
        }

        // Generate invoice number
        $invoiceNumber = $this->generateInvoiceNumber();

        $invoice = Invoice::create([
            'org_id' => auth()->user()->org_id,
            'tenant_id' => $request->tenant_id,
            'property_id' => $request->property_id,
            'invoice_number' => $invoiceNumber,
            'type' => $request->type,
            'amount' => $request->amount,
            'due_date' => $request->due_date,
            'description' => $request->description,
            'items' => $request->items ? json_encode($request->items) : null,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invoice created successfully',
            'data' => $invoice->load(['organization', 'tenant', 'property']),
        ], 201);
    }

    /**
     * Display the specified invoice
     */
    public function show(Invoice $invoice): JsonResponse
    {
        // Check if invoice belongs to user's organization
        if ($invoice->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this invoice',
            ], 403);
        }

        $invoice->load(['organization', 'tenant', 'property', 'payments']);

        return response()->json([
            'success' => true,
            'data' => $invoice,
        ]);
    }

    /**
     * Update the specified invoice
     */
    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        // Check if invoice belongs to user's organization
        if ($invoice->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this invoice',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'tenant_id' => 'sometimes|required|exists:tenants,id',
            'property_id' => 'sometimes|required|exists:properties,id',
            'type' => 'sometimes|required|in:rent,maintenance,penalty,other',
            'amount' => 'sometimes|required|numeric|min:0',
            'due_date' => 'sometimes|required|date',
            'description' => 'nullable|string|max:1000',
            'items' => 'nullable|array',
            'items.*.description' => 'required_with:items|string|max:255',
            'items.*.quantity' => 'required_with:items|numeric|min:0',
            'items.*.rate' => 'required_with:items|numeric|min:0',
            'items.*.amount' => 'required_with:items|numeric|min:0',
            'status' => 'sometimes|required|in:pending,paid,overdue,cancelled',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $invoice->update($request->only([
            'tenant_id', 'property_id', 'type', 'amount', 'due_date',
            'description', 'items', 'status', 'notes'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Invoice updated successfully',
            'data' => $invoice->load(['organization', 'tenant', 'property']),
        ]);
    }

    /**
     * Remove the specified invoice
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        // Check if invoice belongs to user's organization
        if ($invoice->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this invoice',
            ], 403);
        }

        $invoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Invoice deleted successfully',
        ]);
    }

    /**
     * Download invoice as PDF
     */
    public function downloadPdf(Invoice $invoice): JsonResponse
    {
        // Check if invoice belongs to user's organization
        if ($invoice->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to download this invoice',
            ], 403);
        }

        $invoice->load(['organization', 'tenant', 'property', 'payments']);

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
        
        $filename = "invoice-{$invoice->invoice_number}.pdf";
        
        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Send invoice via email
     */
    public function send(Invoice $invoice): JsonResponse
    {
        // Check if invoice belongs to user's organization
        if ($invoice->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to send this invoice',
            ], 403);
        }

        try {
            // Load invoice with relationships
            $invoice->load(['organization', 'tenant', 'property']);
            
            // Generate PDF for attachment
            $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
            $pdfPath = storage_path('app/temp/invoice-' . $invoice->id . '.pdf');
            
            // Ensure temp directory exists
            if (!file_exists(dirname($pdfPath))) {
                mkdir(dirname($pdfPath), 0755, true);
            }
            
            $pdf->save($pdfPath);
            
            // Send invoice email
            $this->emailService->sendInvoiceEmail($invoice, $invoice->tenant, $pdfPath);
            
            // Clean up temp file
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
            }
            
            // Update invoice status to sent
            $invoice->update(['status' => 'sent']);
            
            return response()->json([
                'success' => true,
                'message' => 'Invoice sent successfully to ' . $invoice->tenant->email,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send invoice: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate monthly rent invoices for all active tenants
     */
    public function generateMonthlyRentInvoices(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'month' => 'required|date_format:Y-m',
            'due_date' => 'required|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $month = $request->month;
        $dueDate = $request->due_date;

        // Get all active tenants
        $tenants = Tenant::where('org_id', auth()->user()->org_id)
            ->where('status', 'active')
            ->with('property')
            ->get();

        $generatedInvoices = [];

        foreach ($tenants as $tenant) {
            // Check if invoice already exists for this month
            $existingInvoice = Invoice::where('org_id', auth()->user()->org_id)
                ->where('tenant_id', $tenant->id)
                ->where('type', 'rent')
                ->where('description', 'like', "%{$month}%")
                ->first();

            if (!$existingInvoice) {
                $invoiceNumber = $this->generateInvoiceNumber();
                
                $invoice = Invoice::create([
                    'org_id' => auth()->user()->org_id,
                    'tenant_id' => $tenant->id,
                    'property_id' => $tenant->property_id,
                    'invoice_number' => $invoiceNumber,
                    'type' => 'rent',
                    'amount' => $tenant->rent_amount,
                    'due_date' => $dueDate,
                    'description' => "Monthly rent for {$month}",
                    'status' => 'pending',
                ]);

                $generatedInvoices[] = $invoice;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Monthly rent invoices generated successfully',
            'data' => [
                'generated_count' => count($generatedInvoices),
                'invoices' => $generatedInvoices,
            ],
        ]);
    }

    /**
     * Get invoice statistics
     */
    public function statistics(): JsonResponse
    {
        $orgId = auth()->user()->org_id;

        $stats = [
            'total_invoices' => Invoice::where('org_id', $orgId)->count(),
            'pending_invoices' => Invoice::where('org_id', $orgId)->where('status', 'pending')->count(),
            'paid_invoices' => Invoice::where('org_id', $orgId)->where('status', 'paid')->count(),
            'overdue_invoices' => Invoice::where('org_id', $orgId)->where('status', 'overdue')->count(),
            'total_amount' => Invoice::where('org_id', $orgId)->sum('amount'),
            'paid_amount' => Invoice::where('org_id', $orgId)->where('status', 'paid')->sum('amount'),
            'pending_amount' => Invoice::where('org_id', $orgId)->where('status', 'pending')->sum('amount'),
            'overdue_amount' => Invoice::where('org_id', $orgId)->where('status', 'overdue')->sum('amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');
        
        // Get the last invoice number for this month
        $lastInvoice = Invoice::where('org_id', auth()->user()->org_id)
            ->where('invoice_number', 'like', "{$prefix}-{$year}{$month}%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $newNumber);
    }
}
