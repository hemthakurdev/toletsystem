<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Document;
use App\Models\Lead;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Services\EmailService;

class BulkOperationsController extends Controller
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Bulk delete properties
     */
    public function bulkDeleteProperties(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_ids' => 'required|array|min:1',
            'property_ids.*' => 'integer|exists:properties,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $properties = Property::whereIn('id', $request->property_ids)
                ->where('org_id', auth()->user()->org_id)
                ->get();

            $deletedCount = 0;
            foreach ($properties as $property) {
                // Delete associated images
                if ($property->images) {
                    foreach ($property->images as $image) {
                        Storage::disk('public')->delete($image);
                    }
                }
                
                $property->delete();
                $deletedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} properties",
                'data' => ['deleted_count' => $deletedCount],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete properties: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk update property status
     */
    public function bulkUpdatePropertyStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_ids' => 'required|array|min:1',
            'property_ids.*' => 'integer|exists:properties,id',
            'status' => 'required|in:active,inactive,pending',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $updatedCount = Property::whereIn('id', $request->property_ids)
                ->where('org_id', auth()->user()->org_id)
                ->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => "Successfully updated {$updatedCount} properties",
                'data' => ['updated_count' => $updatedCount],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update properties: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk delete tenants
     */
    public function bulkDeleteTenants(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tenant_ids' => 'required|array|min:1',
            'tenant_ids.*' => 'integer|exists:tenants,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $tenants = Tenant::whereIn('id', $request->tenant_ids)
                ->where('org_id', auth()->user()->org_id)
                ->get();

            $deletedCount = 0;
            foreach ($tenants as $tenant) {
                // Check if tenant has active invoices
                $activeInvoices = Invoice::where('tenant_id', $tenant->id)
                    ->where('status', 'pending')
                    ->count();

                if ($activeInvoices > 0) {
                    continue; // Skip tenants with pending invoices
                }

                $tenant->delete();
                $deletedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} tenants",
                'data' => ['deleted_count' => $deletedCount],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tenants: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk generate invoices
     */
    public function bulkGenerateInvoices(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tenant_ids' => 'required|array|min:1',
            'tenant_ids.*' => 'integer|exists:tenants,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'due_date' => 'required|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $tenants = Tenant::whereIn('id', $request->tenant_ids)
                ->where('org_id', auth()->user()->org_id)
                ->get();

            $generatedCount = 0;
            $invoices = [];

            foreach ($tenants as $tenant) {
                $invoice = Invoice::create([
                    'org_id' => auth()->user()->org_id,
                    'tenant_id' => $tenant->id,
                    'property_id' => $tenant->property_id,
                    'invoice_number' => 'INV-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT),
                    'amount' => $request->amount,
                    'description' => $request->description,
                    'due_date' => $request->due_date,
                    'status' => 'pending',
                ]);

                $invoices[] = $invoice;
                $generatedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully generated {$generatedCount} invoices",
                'data' => [
                    'generated_count' => $generatedCount,
                    'invoices' => $invoices,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate invoices: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk approve expenses
     */
    public function bulkApproveExpenses(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'expense_ids' => 'required|array|min:1',
            'expense_ids.*' => 'integer|exists:expenses,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $updatedCount = Expense::whereIn('id', $request->expense_ids)
                ->where('org_id', auth()->user()->org_id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully approved {$updatedCount} expenses",
                'data' => ['approved_count' => $updatedCount],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve expenses: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk reject expenses
     */
    public function bulkRejectExpenses(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'expense_ids' => 'required|array|min:1',
            'expense_ids.*' => 'integer|exists:expenses,id',
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $updatedCount = Expense::whereIn('id', $request->expense_ids)
                ->where('org_id', auth()->user()->org_id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'rejected',
                    'rejection_reason' => $request->rejection_reason,
                ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully rejected {$updatedCount} expenses",
                'data' => ['rejected_count' => $updatedCount],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject expenses: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk delete documents
     */
    public function bulkDeleteDocuments(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'document_ids' => 'required|array|min:1',
            'document_ids.*' => 'integer|exists:documents,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $documents = Document::whereIn('id', $request->document_ids)
                ->where('org_id', auth()->user()->org_id)
                ->get();

            $deletedCount = 0;
            foreach ($documents as $document) {
                // Delete file from storage
                if ($document->file_path) {
                    Storage::disk('public')->delete($document->file_path);
                }
                
                $document->delete();
                $deletedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} documents",
                'data' => ['deleted_count' => $deletedCount],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete documents: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk update lead status
     */
    public function bulkUpdateLeadStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lead_ids' => 'required|array|min:1',
            'lead_ids.*' => 'integer|exists:leads,id',
            'status' => 'required|in:new,contacted,interested,not_interested,converted',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $updateData = ['status' => $request->status];
            
            if ($request->status === 'contacted') {
                $updateData['contacted_at'] = now();
            } elseif ($request->status === 'converted') {
                $updateData['converted_at'] = now();
            }

            $updatedCount = Lead::whereIn('id', $request->lead_ids)
                ->whereHas('property', function ($query) {
                    $query->where('org_id', auth()->user()->org_id);
                })
                ->update($updateData);

            return response()->json([
                'success' => true,
                'message' => "Successfully updated {$updatedCount} leads",
                'data' => ['updated_count' => $updatedCount],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update leads: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk send notifications
     */
    public function bulkSendNotifications(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|in:info,warning,success,error',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $users = User::whereIn('id', $request->user_ids)
                ->where('org_id', auth()->user()->org_id)
                ->get();

            $sentCount = 0;
            foreach ($users as $user) {
                $user->notifications()->create([
                    'title' => $request->title,
                    'message' => $request->message,
                    'type' => $request->type,
                    'read_at' => null,
                ]);
                $sentCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully sent {$sentCount} notifications",
                'data' => ['sent_count' => $sentCount],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notifications: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk import properties from CSV
     */
    public function bulkImportProperties(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $file = $request->file('csv_file');
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
            $header = array_shift($csvData);

            $importedCount = 0;
            $errors = [];

            foreach ($csvData as $index => $row) {
                try {
                    $data = array_combine($header, $row);
                    
                    Property::create([
                        'org_id' => auth()->user()->org_id,
                        'title' => $data['title'] ?? 'Imported Property',
                        'description' => $data['description'] ?? '',
                        'property_type' => $data['property_type'] ?? 'apartment',
                        'category' => $data['category'] ?? 'rent',
                        'price' => $data['price'] ?? 0,
                        'bedrooms' => $data['bedrooms'] ?? 1,
                        'bathrooms' => $data['bathrooms'] ?? 1,
                        'area_sqft' => $data['area_sqft'] ?? 0,
                        'address' => $data['address'] ?? '',
                        'city' => $data['city'] ?? '',
                        'state' => $data['state'] ?? '',
                        'pincode' => $data['pincode'] ?? '',
                        'status' => 'active',
                        'published' => false,
                    ]);

                    $importedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$importedCount} properties",
                'data' => [
                    'imported_count' => $importedCount,
                    'errors' => $errors,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to import properties: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get bulk operation statistics
     */
    public function getBulkOperationStats(): JsonResponse
    {
        try {
            $orgId = auth()->user()->org_id;

            $stats = [
                'properties' => [
                    'total' => Property::where('org_id', $orgId)->count(),
                    'active' => Property::where('org_id', $orgId)->where('status', 'active')->count(),
                    'pending' => Property::where('org_id', $orgId)->where('status', 'pending')->count(),
                ],
                'tenants' => [
                    'total' => Tenant::where('org_id', $orgId)->count(),
                    'active' => Tenant::where('org_id', $orgId)->where('status', 'active')->count(),
                ],
                'invoices' => [
                    'total' => Invoice::where('org_id', $orgId)->count(),
                    'pending' => Invoice::where('org_id', $orgId)->where('status', 'pending')->count(),
                ],
                'expenses' => [
                    'total' => Expense::where('org_id', $orgId)->count(),
                    'pending' => Expense::where('org_id', $orgId)->where('status', 'pending')->count(),
                ],
                'documents' => [
                    'total' => Document::where('org_id', $orgId)->count(),
                ],
                'leads' => [
                    'total' => Lead::whereHas('property', function ($query) use ($orgId) {
                        $query->where('org_id', $orgId);
                    })->count(),
                    'new' => Lead::whereHas('property', function ($query) use ($orgId) {
                        $query->where('org_id', $orgId);
                    })->where('status', 'new')->count(),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage(),
            ], 500);
        }
    }
}