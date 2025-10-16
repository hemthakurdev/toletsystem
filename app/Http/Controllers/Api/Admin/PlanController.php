<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PlanController extends Controller
{
    /**
     * Display a listing of plans
     */
    public function index(): JsonResponse
    {
        $plans = Plan::withCount('organizations')->get();

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    /**
     * Store a newly created plan
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
            'max_properties' => 'required|integer|min:1',
            'max_users' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $plan = Plan::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Plan created successfully',
                'data' => $plan,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create plan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified plan
     */
    public function show(Plan $plan): JsonResponse
    {
        $plan->loadCount('organizations');

        return response()->json([
            'success' => true,
            'data' => $plan,
        ]);
    }

    /**
     * Update the specified plan
     */
    public function update(Request $request, Plan $plan): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'billing_cycle' => 'sometimes|in:monthly,yearly',
            'max_properties' => 'sometimes|integer|min:1',
            'max_users' => 'sometimes|integer|min:1',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $plan->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Plan updated successfully',
                'data' => $plan,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update plan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified plan
     */
    public function destroy(Plan $plan): JsonResponse
    {
        try {
            // Check if plan has active organizations
            if ($plan->organizations()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete plan with active organizations',
                ], 400);
            }

            $plan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Plan deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete plan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
