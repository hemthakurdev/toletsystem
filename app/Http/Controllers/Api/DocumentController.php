<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents for the organization
     */
    public function index(Request $request): JsonResponse
    {
        $query = Document::with(['property', 'tenant', 'invoice'])
            ->where('org_id', auth()->user()->org_id);

        // Apply filters
        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->has('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('document_type', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $documents = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $documents,
        ]);
    }

    /**
     * Store a newly created document
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'document_type' => 'required|string|max:100',
            'category' => 'required|string|max:100',
            'property_id' => 'nullable|exists:properties,id',
            'tenant_id' => 'nullable|exists:tenants,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif|max:20480', // 20MB
            'is_public' => 'boolean',
            'expiry_date' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Verify property belongs to organization if provided
        if ($request->property_id) {
            $property = Property::where('id', $request->property_id)
                ->where('org_id', auth()->user()->org_id)
                ->first();

            if (!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property not found or unauthorized',
                ], 404);
            }
        }

        // Verify tenant belongs to organization if provided
        if ($request->tenant_id) {
            $tenant = Tenant::where('id', $request->tenant_id)
                ->where('org_id', auth()->user()->org_id)
                ->first();

            if (!$tenant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tenant not found or unauthorized',
                ], 404);
            }
        }

        try {
            // Handle file upload
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('documents', $filename, 'public');

            $document = Document::create([
                'org_id' => auth()->user()->org_id,
                'title' => $request->title,
                'description' => $request->description,
                'document_type' => $request->document_type,
                'category' => $request->category,
                'property_id' => $request->property_id,
                'tenant_id' => $request->tenant_id,
                'invoice_id' => $request->invoice_id,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'is_public' => $request->boolean('is_public', false),
                'expiry_date' => $request->expiry_date,
                'uploaded_by' => auth()->id(),
            ]);

            $document->load(['property', 'tenant', 'invoice']);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'data' => $document,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified document
     */
    public function show(Document $document): JsonResponse
    {
        // Check if document belongs to user's organization
        if ($document->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this document',
            ], 403);
        }

        $document->load(['property', 'tenant', 'invoice', 'uploadedBy']);

        return response()->json([
            'success' => true,
            'data' => $document,
        ]);
    }

    /**
     * Update the specified document
     */
    public function update(Request $request, Document $document): JsonResponse
    {
        // Check if document belongs to user's organization
        if ($document->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this document',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'document_type' => 'sometimes|string|max:100',
            'category' => 'sometimes|string|max:100',
            'property_id' => 'nullable|exists:properties,id',
            'tenant_id' => 'nullable|exists:tenants,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif|max:20480',
            'is_public' => 'boolean',
            'expiry_date' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $updateData = $request->only([
                'title', 'description', 'document_type', 'category',
                'property_id', 'tenant_id', 'invoice_id', 'is_public', 'expiry_date'
            ]);

            // Handle file replacement
            if ($request->hasFile('file')) {
                // Delete old file
                if ($document->file_path) {
                    Storage::disk('public')->delete($document->file_path);
                }

                $file = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('documents', $filename, 'public');

                $updateData['file_path'] = $path;
                $updateData['file_name'] = $file->getClientOriginalName();
                $updateData['file_size'] = $file->getSize();
                $updateData['mime_type'] = $file->getMimeType();
            }

            $document->update($updateData);
            $document->load(['property', 'tenant', 'invoice']);

            return response()->json([
                'success' => true,
                'message' => 'Document updated successfully',
                'data' => $document,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update document: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified document
     */
    public function destroy(Document $document): JsonResponse
    {
        // Check if document belongs to user's organization
        if ($document->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this document',
            ], 403);
        }

        try {
            // Delete file from storage
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }

            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download the document file
     */
    public function download(Document $document): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        // Check if document belongs to user's organization
        if ($document->org_id !== auth()->user()->org_id) {
            abort(403, 'Unauthorized to download this document');
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * Get document statistics
     */
    public function statistics(): JsonResponse
    {
        $orgId = auth()->user()->org_id;

        $stats = [
            'total_documents' => Document::where('org_id', $orgId)->count(),
            'total_size' => Document::where('org_id', $orgId)->sum('file_size'),
            'documents_by_type' => Document::where('org_id', $orgId)
                ->selectRaw('document_type, COUNT(*) as count')
                ->groupBy('document_type')
                ->get(),
            'documents_by_category' => Document::where('org_id', $orgId)
                ->selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->get(),
            'recent_documents' => Document::where('org_id', $orgId)
                ->with(['property:id,title', 'tenant:id,name'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'expiring_soon' => Document::where('org_id', $orgId)
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now()->addDays(30))
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get document categories
     */
    public function categories(): JsonResponse
    {
        $categories = [
            'lease_agreement' => 'Lease Agreement',
            'rent_receipt' => 'Rent Receipt',
            'maintenance_bill' => 'Maintenance Bill',
            'utility_bill' => 'Utility Bill',
            'insurance_document' => 'Insurance Document',
            'property_tax' => 'Property Tax',
            'tenant_id_proof' => 'Tenant ID Proof',
            'tenant_agreement' => 'Tenant Agreement',
            'inspection_report' => 'Inspection Report',
            'legal_document' => 'Legal Document',
            'financial_document' => 'Financial Document',
            'other' => 'Other',
        ];

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get document types
     */
    public function types(): JsonResponse
    {
        $types = [
            'pdf' => 'PDF Document',
            'image' => 'Image File',
            'spreadsheet' => 'Spreadsheet',
            'word_document' => 'Word Document',
            'text' => 'Text File',
            'other' => 'Other',
        ];

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }
}
