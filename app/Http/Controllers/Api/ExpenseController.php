<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses for the organization
     */
    public function index(Request $request): JsonResponse
    {
        $query = Expense::with(['property', 'category'])
            ->where('org_id', auth()->user()->org_id);

        // Apply filters
        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        if ($request->has('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }

        if ($request->has('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('vendor', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'expense_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $expenses = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $expenses,
        ]);
    }

    /**
     * Store a newly created expense
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'category' => 'required|string|max:100',
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'vendor' => 'nullable|string|max:255',
            'receipt_number' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Verify property belongs to organization
        $property = Property::where('id', $request->property_id)
            ->where('org_id', auth()->user()->org_id)
            ->first();

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found or unauthorized',
            ], 404);
        }

        try {
            $expense = Expense::create([
                'org_id' => auth()->user()->org_id,
                'property_id' => $request->property_id,
                'category' => $request->category,
                'description' => $request->description,
                'amount' => $request->amount,
                'expense_date' => $request->expense_date,
                'vendor' => $request->vendor,
                'receipt_number' => $request->receipt_number,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            // Handle receipt file upload
            if ($request->hasFile('receipt_file')) {
                $file = $request->file('receipt_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('expenses/receipts', $filename, 'public');
                $expense->update(['receipt_file' => $path]);
            }

            $expense->load(['property', 'category']);

            return response()->json([
                'success' => true,
                'message' => 'Expense created successfully',
                'data' => $expense,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create expense: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified expense
     */
    public function show(Expense $expense): JsonResponse
    {
        // Check if expense belongs to user's organization
        if ($expense->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this expense',
            ], 403);
        }

        $expense->load(['property', 'category', 'createdBy']);

        return response()->json([
            'success' => true,
            'data' => $expense,
        ]);
    }

    /**
     * Update the specified expense
     */
    public function update(Request $request, Expense $expense): JsonResponse
    {
        // Check if expense belongs to user's organization
        if ($expense->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this expense',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'property_id' => 'sometimes|exists:properties,id',
            'category' => 'sometimes|string|max:100',
            'description' => 'sometimes|string|max:500',
            'amount' => 'sometimes|numeric|min:0.01',
            'expense_date' => 'sometimes|date',
            'vendor' => 'nullable|string|max:255',
            'receipt_number' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'status' => 'sometimes|in:pending,approved,rejected',
            'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
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
                'property_id', 'category', 'description', 'amount',
                'expense_date', 'vendor', 'receipt_number', 'payment_method',
                'notes', 'status'
            ]);

            $expense->update($updateData);

            // Handle receipt file upload
            if ($request->hasFile('receipt_file')) {
                // Delete old receipt if exists
                if ($expense->receipt_file) {
                    \Storage::disk('public')->delete($expense->receipt_file);
                }

                $file = $request->file('receipt_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('expenses/receipts', $filename, 'public');
                $expense->update(['receipt_file' => $path]);
            }

            $expense->load(['property', 'category']);

            return response()->json([
                'success' => true,
                'message' => 'Expense updated successfully',
                'data' => $expense,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update expense: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified expense
     */
    public function destroy(Expense $expense): JsonResponse
    {
        // Check if expense belongs to user's organization
        if ($expense->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this expense',
            ], 403);
        }

        try {
            // Delete receipt file if exists
            if ($expense->receipt_file) {
                \Storage::disk('public')->delete($expense->receipt_file);
            }

            $expense->delete();

            return response()->json([
                'success' => true,
                'message' => 'Expense deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete expense: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve an expense
     */
    public function approve(Expense $expense): JsonResponse
    {
        // Check if expense belongs to user's organization
        if ($expense->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to approve this expense',
            ], 403);
        }

        try {
            $expense->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Expense approved successfully',
                'data' => $expense,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve expense: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject an expense
     */
    public function reject(Request $request, Expense $expense): JsonResponse
    {
        // Check if expense belongs to user's organization
        if ($expense->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to reject this expense',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
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
            $expense->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Expense rejected successfully',
                'data' => $expense,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject expense: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get expense statistics
     */
    public function statistics(): JsonResponse
    {
        $orgId = auth()->user()->org_id;

        $stats = [
            'total_expenses' => Expense::where('org_id', $orgId)->count(),
            'total_amount' => Expense::where('org_id', $orgId)->sum('amount'),
            'pending_expenses' => Expense::where('org_id', $orgId)->where('status', 'pending')->count(),
            'approved_expenses' => Expense::where('org_id', $orgId)->where('status', 'approved')->count(),
            'rejected_expenses' => Expense::where('org_id', $orgId)->where('status', 'rejected')->count(),
            'monthly_expenses' => Expense::where('org_id', $orgId)
                ->whereMonth('expense_date', now()->month)
                ->whereYear('expense_date', now()->year)
                ->sum('amount'),
            'expenses_by_category' => Expense::where('org_id', $orgId)
                ->selectRaw('category, COUNT(*) as count, SUM(amount) as total')
                ->groupBy('category')
                ->get(),
            'expenses_by_property' => Expense::where('org_id', $orgId)
                ->with('property:id,title')
                ->selectRaw('property_id, COUNT(*) as count, SUM(amount) as total')
                ->groupBy('property_id')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
