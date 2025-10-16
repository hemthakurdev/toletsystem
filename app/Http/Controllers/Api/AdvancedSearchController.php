<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Document;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AdvancedSearchController extends Controller
{
    /**
     * Advanced property search with multiple filters
     */
    public function advancedPropertySearch(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'nullable|string|max:255',
            'filters' => 'array',
            'filters.property_type' => 'array',
            'filters.property_type.*' => 'in:apartment,house,villa,commercial,land',
            'filters.category' => 'array',
            'filters.category.*' => 'in:rent,sale',
            'filters.status' => 'array',
            'filters.status.*' => 'in:active,inactive,pending',
            'filters.published' => 'boolean',
            'filters.price_min' => 'numeric|min:0',
            'filters.price_max' => 'numeric|min:0|gte:filters.price_min',
            'filters.bedrooms_min' => 'integer|min:0',
            'filters.bedrooms_max' => 'integer|min:0|gte:filters.bedrooms_min',
            'filters.bathrooms_min' => 'integer|min:0',
            'filters.bathrooms_max' => 'integer|min:0|gte:filters.bathrooms_min',
            'filters.area_min' => 'numeric|min:0',
            'filters.area_max' => 'numeric|min:0|gte:filters.area_min',
            'filters.cities' => 'array',
            'filters.cities.*' => 'string|max:100',
            'filters.states' => 'array',
            'filters.states.*' => 'string|max:100',
            'filters.pincodes' => 'array',
            'filters.pincodes.*' => 'string|max:10',
            'filters.amenities' => 'array',
            'filters.amenities.*' => 'string|max:100',
            'filters.date_from' => 'date',
            'filters.date_to' => 'date|after_or_equal:filters.date_from',
            'sort_by' => 'in:price,created_at,updated_at,bedrooms,bathrooms,area_sqft',
            'sort_order' => 'in:asc,desc',
            'per_page' => 'integer|min:1|max:100',
            'page' => 'integer|min:1',
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

            // Text search
            if ($request->filled('query')) {
                $searchTerm = $request->query;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                      ->orWhere('description', 'like', "%{$searchTerm}%")
                      ->orWhere('address', 'like', "%{$searchTerm}%")
                      ->orWhere('city', 'like', "%{$searchTerm}%")
                      ->orWhere('state', 'like', "%{$searchTerm}%");
                });
            }

            // Apply filters
            if ($request->has('filters')) {
                $filters = $request->filters;

                if (isset($filters['property_type']) && !empty($filters['property_type'])) {
                    $query->whereIn('property_type', $filters['property_type']);
                }

                if (isset($filters['category']) && !empty($filters['category'])) {
                    $query->whereIn('category', $filters['category']);
                }

                if (isset($filters['status']) && !empty($filters['status'])) {
                    $query->whereIn('status', $filters['status']);
                }

                if (isset($filters['published'])) {
                    $query->where('published', $filters['published']);
                }

                if (isset($filters['price_min'])) {
                    $query->where('price', '>=', $filters['price_min']);
                }

                if (isset($filters['price_max'])) {
                    $query->where('price', '<=', $filters['price_max']);
                }

                if (isset($filters['bedrooms_min'])) {
                    $query->where('bedrooms', '>=', $filters['bedrooms_min']);
                }

                if (isset($filters['bedrooms_max'])) {
                    $query->where('bedrooms', '<=', $filters['bedrooms_max']);
                }

                if (isset($filters['bathrooms_min'])) {
                    $query->where('bathrooms', '>=', $filters['bathrooms_min']);
                }

                if (isset($filters['bathrooms_max'])) {
                    $query->where('bathrooms', '<=', $filters['bathrooms_max']);
                }

                if (isset($filters['area_min'])) {
                    $query->where('area_sqft', '>=', $filters['area_min']);
                }

                if (isset($filters['area_max'])) {
                    $query->where('area_sqft', '<=', $filters['area_max']);
                }

                if (isset($filters['cities']) && !empty($filters['cities'])) {
                    $query->whereIn('city', $filters['cities']);
                }

                if (isset($filters['states']) && !empty($filters['states'])) {
                    $query->whereIn('state', $filters['states']);
                }

                if (isset($filters['pincodes']) && !empty($filters['pincodes'])) {
                    $query->whereIn('pincode', $filters['pincodes']);
                }

                if (isset($filters['amenities']) && !empty($filters['amenities'])) {
                    foreach ($filters['amenities'] as $amenity) {
                        $query->whereJsonContains('amenities', $amenity);
                    }
                }

                if (isset($filters['date_from'])) {
                    $query->whereDate('created_at', '>=', $filters['date_from']);
                }

                if (isset($filters['date_to'])) {
                    $query->whereDate('created_at', '<=', $filters['date_to']);
                }
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $properties = $query->with(['tenants', 'invoices'])->paginate($perPage);

            // Get search suggestions
            $suggestions = $this->getPropertySearchSuggestions($request);

            return response()->json([
                'success' => true,
                'data' => [
                    'properties' => $properties,
                    'suggestions' => $suggestions,
                    'filters_applied' => $request->filters ?? [],
                    'total_results' => $properties->total(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Advanced tenant search
     */
    public function advancedTenantSearch(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'nullable|string|max:255',
            'filters' => 'array',
            'filters.status' => 'array',
            'filters.status.*' => 'in:active,inactive',
            'filters.property_id' => 'array',
            'filters.property_id.*' => 'integer|exists:properties,id',
            'filters.rent_min' => 'numeric|min:0',
            'filters.rent_max' => 'numeric|min:0|gte:filters.rent_min',
            'filters.move_in_from' => 'date',
            'filters.move_in_to' => 'date|after_or_equal:filters.move_in_from',
            'filters.move_out_from' => 'date',
            'filters.move_out_to' => 'date|after_or_equal:filters.move_out_from',
            'filters.has_pending_invoices' => 'boolean',
            'filters.has_overdue_invoices' => 'boolean',
            'sort_by' => 'in:name,email,rent_amount,move_in_date,created_at',
            'sort_order' => 'in:asc,desc',
            'per_page' => 'integer|min:1|max:100',
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

            // Text search
            if ($request->filled('query')) {
                $searchTerm = $request->query;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('email', 'like', "%{$searchTerm}%")
                      ->orWhere('phone', 'like', "%{$searchTerm}%");
                });
            }

            // Apply filters
            if ($request->has('filters')) {
                $filters = $request->filters;

                if (isset($filters['status']) && !empty($filters['status'])) {
                    $query->whereIn('status', $filters['status']);
                }

                if (isset($filters['property_id']) && !empty($filters['property_id'])) {
                    $query->whereIn('property_id', $filters['property_id']);
                }

                if (isset($filters['rent_min'])) {
                    $query->where('rent_amount', '>=', $filters['rent_min']);
                }

                if (isset($filters['rent_max'])) {
                    $query->where('rent_amount', '<=', $filters['rent_max']);
                }

                if (isset($filters['move_in_from'])) {
                    $query->whereDate('move_in_date', '>=', $filters['move_in_from']);
                }

                if (isset($filters['move_in_to'])) {
                    $query->whereDate('move_in_date', '<=', $filters['move_in_to']);
                }

                if (isset($filters['move_out_from'])) {
                    $query->whereDate('move_out_date', '>=', $filters['move_out_from']);
                }

                if (isset($filters['move_out_to'])) {
                    $query->whereDate('move_out_date', '<=', $filters['move_out_to']);
                }

                if (isset($filters['has_pending_invoices'])) {
                    if ($filters['has_pending_invoices']) {
                        $query->whereHas('invoices', function ($q) {
                            $q->where('status', 'pending');
                        });
                    } else {
                        $query->whereDoesntHave('invoices', function ($q) {
                            $q->where('status', 'pending');
                        });
                    }
                }

                if (isset($filters['has_overdue_invoices'])) {
                    if ($filters['has_overdue_invoices']) {
                        $query->whereHas('invoices', function ($q) {
                            $q->where('status', 'overdue');
                        });
                    } else {
                        $query->whereDoesntHave('invoices', function ($q) {
                            $q->where('status', 'overdue');
                        });
                    }
                }
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $tenants = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'tenants' => $tenants,
                    'filters_applied' => $request->filters ?? [],
                    'total_results' => $tenants->total(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Advanced invoice search
     */
    public function advancedInvoiceSearch(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'nullable|string|max:255',
            'filters' => 'array',
            'filters.status' => 'array',
            'filters.status.*' => 'in:pending,paid,overdue,cancelled',
            'filters.tenant_id' => 'array',
            'filters.tenant_id.*' => 'integer|exists:tenants,id',
            'filters.property_id' => 'array',
            'filters.property_id.*' => 'integer|exists:properties,id',
            'filters.amount_min' => 'numeric|min:0',
            'filters.amount_max' => 'numeric|min:0|gte:filters.amount_min',
            'filters.due_date_from' => 'date',
            'filters.due_date_to' => 'date|after_or_equal:filters.due_date_from',
            'filters.created_from' => 'date',
            'filters.created_to' => 'date|after_or_equal:filters.created_from',
            'sort_by' => 'in:amount,due_date,created_at,status',
            'sort_order' => 'in:asc,desc',
            'per_page' => 'integer|min:1|max:100',
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

            // Text search
            if ($request->filled('query')) {
                $searchTerm = $request->query;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('invoice_number', 'like', "%{$searchTerm}%")
                      ->orWhere('description', 'like', "%{$searchTerm}%")
                      ->orWhereHas('tenant', function ($tenantQuery) use ($searchTerm) {
                          $tenantQuery->where('name', 'like', "%{$searchTerm}%")
                                     ->orWhere('email', 'like', "%{$searchTerm}%");
                      });
                });
            }

            // Apply filters
            if ($request->has('filters')) {
                $filters = $request->filters;

                if (isset($filters['status']) && !empty($filters['status'])) {
                    $query->whereIn('status', $filters['status']);
                }

                if (isset($filters['tenant_id']) && !empty($filters['tenant_id'])) {
                    $query->whereIn('tenant_id', $filters['tenant_id']);
                }

                if (isset($filters['property_id']) && !empty($filters['property_id'])) {
                    $query->whereIn('property_id', $filters['property_id']);
                }

                if (isset($filters['amount_min'])) {
                    $query->where('amount', '>=', $filters['amount_min']);
                }

                if (isset($filters['amount_max'])) {
                    $query->where('amount', '<=', $filters['amount_max']);
                }

                if (isset($filters['due_date_from'])) {
                    $query->whereDate('due_date', '>=', $filters['due_date_from']);
                }

                if (isset($filters['due_date_to'])) {
                    $query->whereDate('due_date', '<=', $filters['due_date_to']);
                }

                if (isset($filters['created_from'])) {
                    $query->whereDate('created_at', '>=', $filters['created_from']);
                }

                if (isset($filters['created_to'])) {
                    $query->whereDate('created_at', '<=', $filters['created_to']);
                }
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $invoices = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'invoices' => $invoices,
                    'filters_applied' => $request->filters ?? [],
                    'total_results' => $invoices->total(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Global search across all entities
     */
    public function globalSearch(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2|max:255',
            'entities' => 'array',
            'entities.*' => 'in:properties,tenants,invoices,expenses,documents,leads',
            'limit' => 'integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $searchTerm = $request->query;
            $entities = $request->get('entities', ['properties', 'tenants', 'invoices']);
            $limit = $request->get('limit', 10);
            $orgId = auth()->user()->org_id;
            $results = [];

            // Search properties
            if (in_array('properties', $entities)) {
                $properties = Property::where('org_id', $orgId)
                    ->where(function ($q) use ($searchTerm) {
                        $q->where('title', 'like', "%{$searchTerm}%")
                          ->orWhere('description', 'like', "%{$searchTerm}%")
                          ->orWhere('address', 'like', "%{$searchTerm}%");
                    })
                    ->limit($limit)
                    ->get(['id', 'title', 'address', 'city', 'price', 'status']);

                $results['properties'] = $properties;
            }

            // Search tenants
            if (in_array('tenants', $entities)) {
                $tenants = Tenant::where('org_id', $orgId)
                    ->where(function ($q) use ($searchTerm) {
                        $q->where('name', 'like', "%{$searchTerm}%")
                          ->orWhere('email', 'like', "%{$searchTerm}%")
                          ->orWhere('phone', 'like', "%{$searchTerm}%");
                    })
                    ->limit($limit)
                    ->get(['id', 'name', 'email', 'phone', 'status']);

                $results['tenants'] = $tenants;
            }

            // Search invoices
            if (in_array('invoices', $entities)) {
                $invoices = Invoice::where('org_id', $orgId)
                    ->where(function ($q) use ($searchTerm) {
                        $q->where('invoice_number', 'like', "%{$searchTerm}%")
                          ->orWhere('description', 'like', "%{$searchTerm}%");
                    })
                    ->limit($limit)
                    ->get(['id', 'invoice_number', 'amount', 'status', 'due_date']);

                $results['invoices'] = $invoices;
            }

            // Search expenses
            if (in_array('expenses', $entities)) {
                $expenses = Expense::where('org_id', $orgId)
                    ->where(function ($q) use ($searchTerm) {
                        $q->where('description', 'like', "%{$searchTerm}%")
                          ->orWhere('category', 'like', "%{$searchTerm}%");
                    })
                    ->limit($limit)
                    ->get(['id', 'description', 'amount', 'category', 'status']);

                $results['expenses'] = $expenses;
            }

            // Search documents
            if (in_array('documents', $entities)) {
                $documents = Document::where('org_id', $orgId)
                    ->where(function ($q) use ($searchTerm) {
                        $q->where('name', 'like', "%{$searchTerm}%")
                          ->orWhere('description', 'like', "%{$searchTerm}%");
                    })
                    ->limit($limit)
                    ->get(['id', 'name', 'type', 'category', 'created_at']);

                $results['documents'] = $documents;
            }

            // Search leads
            if (in_array('leads', $entities)) {
                $leads = Lead::whereHas('property', function ($q) use ($orgId) {
                    $q->where('org_id', $orgId);
                })
                ->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('email', 'like', "%{$searchTerm}%")
                      ->orWhere('phone', 'like', "%{$searchTerm}%");
                })
                ->limit($limit)
                ->get(['id', 'name', 'email', 'phone', 'status']);

                $results['leads'] = $leads;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'results' => $results,
                    'query' => $searchTerm,
                    'total_results' => array_sum(array_map('count', $results)),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get search suggestions
     */
    public function getSearchSuggestions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:properties,tenants,invoices,global',
            'query' => 'required|string|min:1|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $searchTerm = $request->query;
            $orgId = auth()->user()->org_id;
            $suggestions = [];

            switch ($request->type) {
                case 'properties':
                    $suggestions = $this->getPropertySearchSuggestions($request);
                    break;
                case 'tenants':
                    $suggestions = $this->getTenantSearchSuggestions($request);
                    break;
                case 'invoices':
                    $suggestions = $this->getInvoiceSearchSuggestions($request);
                    break;
                case 'global':
                    $suggestions = $this->getGlobalSearchSuggestions($request);
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => $suggestions,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get suggestions: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get property search suggestions
     */
    private function getPropertySearchSuggestions(Request $request): array
    {
        $orgId = auth()->user()->org_id;
        
        return [
            'cities' => Property::where('org_id', $orgId)
                ->where('city', 'like', '%' . $request->query . '%')
                ->distinct()
                ->pluck('city')
                ->take(5),
            'property_types' => Property::where('org_id', $orgId)
                ->where('property_type', 'like', '%' . $request->query . '%')
                ->distinct()
                ->pluck('property_type')
                ->take(5),
            'titles' => Property::where('org_id', $orgId)
                ->where('title', 'like', '%' . $request->query . '%')
                ->pluck('title')
                ->take(5),
        ];
    }

    /**
     * Get tenant search suggestions
     */
    private function getTenantSearchSuggestions(Request $request): array
    {
        $orgId = auth()->user()->org_id;
        
        return [
            'names' => Tenant::where('org_id', $orgId)
                ->where('name', 'like', '%' . $request->query . '%')
                ->pluck('name')
                ->take(5),
            'emails' => Tenant::where('org_id', $orgId)
                ->where('email', 'like', '%' . $request->query . '%')
                ->pluck('email')
                ->take(5),
        ];
    }

    /**
     * Get invoice search suggestions
     */
    private function getInvoiceSearchSuggestions(Request $request): array
    {
        $orgId = auth()->user()->org_id;
        
        return [
            'invoice_numbers' => Invoice::where('org_id', $orgId)
                ->where('invoice_number', 'like', '%' . $request->query . '%')
                ->pluck('invoice_number')
                ->take(5),
            'descriptions' => Invoice::where('org_id', $orgId)
                ->where('description', 'like', '%' . $request->query . '%')
                ->pluck('description')
                ->take(5),
        ];
    }

    /**
     * Get global search suggestions
     */
    private function getGlobalSearchSuggestions(Request $request): array
    {
        $orgId = auth()->user()->org_id;
        $searchTerm = $request->query;
        
        return [
            'properties' => Property::where('org_id', $orgId)
                ->where('title', 'like', '%' . $searchTerm . '%')
                ->pluck('title')
                ->take(3),
            'tenants' => Tenant::where('org_id', $orgId)
                ->where('name', 'like', '%' . $searchTerm . '%')
                ->pluck('name')
                ->take(3),
            'invoices' => Invoice::where('org_id', $orgId)
                ->where('invoice_number', 'like', '%' . $searchTerm . '%')
                ->pluck('invoice_number')
                ->take(3),
        ];
    }
}