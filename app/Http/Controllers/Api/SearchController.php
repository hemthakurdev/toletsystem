<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * Search properties for public marketplace
     */
    public function properties(Request $request): JsonResponse
    {
        $query = Property::with(['organization'])
            ->where('published', true)
            ->where('availability_status', 'vacant');

        // Apply search filters
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('locality', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%");
            });
        }

        if ($request->has('city')) {
            $query->where('city', 'like', "%{$request->city}%");
        }

        if ($request->has('locality')) {
            $query->where('locality', 'like', "%{$request->locality}%");
        }

        if ($request->has('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        if ($request->has('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->has('min_bedrooms')) {
            $query->where('bedrooms', '>=', $request->min_bedrooms);
        }

        if ($request->has('max_bedrooms')) {
            $query->where('bedrooms', '<=', $request->max_bedrooms);
        }

        if ($request->has('min_bathrooms')) {
            $query->where('bathrooms', '>=', $request->min_bathrooms);
        }

        if ($request->has('max_bathrooms')) {
            $query->where('bathrooms', '<=', $request->max_bathrooms);
        }

        if ($request->has('furnished_status')) {
            $query->where('furnished_status', $request->furnished_status);
        }

        if ($request->has('featured')) {
            $query->where('featured', $request->featured);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Handle special sorting cases
        if ($sortBy === 'price_low_to_high') {
            $query->orderBy('price', 'asc');
        } elseif ($sortBy === 'price_high_to_low') {
            $query->orderBy('price', 'desc');
        } elseif ($sortBy === 'newest') {
            $query->orderBy('created_at', 'desc');
        } elseif ($sortBy === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->get('per_page', 12);
        $properties = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $properties,
        ]);
    }

    /**
     * Get unique cities for search filters
     */
    public function cities(Request $request): JsonResponse
    {
        $cities = Property::where('published', true)
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->pluck('city')
            ->sort()
            ->values();

        return response()->json([
            'success' => true,
            'data' => $cities,
        ]);
    }

    /**
     * Get localities for a specific city
     */
    public function localities(Request $request): JsonResponse
    {
        $query = Property::where('published', true)
            ->whereNotNull('locality')
            ->where('locality', '!=', '');

        if ($request->has('city')) {
            $query->where('city', $request->city);
        }

        $localities = $query->distinct()
            ->pluck('locality')
            ->sort()
            ->values();

        return response()->json([
            'success' => true,
            'data' => $localities,
        ]);
    }

    /**
     * Get property types for search filters
     */
    public function propertyTypes(): JsonResponse
    {
        $types = Property::where('published', true)
            ->whereNotNull('property_type')
            ->distinct()
            ->pluck('property_type')
            ->sort()
            ->values();

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    /**
     * Get featured properties for homepage
     */
    public function featuredProperties(): JsonResponse
    {
        $properties = Property::with(['organization'])
            ->where('published', true)
            ->where('featured', true)
            ->where('availability_status', 'vacant')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $properties,
        ]);
    }

    /**
     * Get recent properties
     */
    public function recentProperties(): JsonResponse
    {
        $properties = Property::with(['organization'])
            ->where('published', true)
            ->where('availability_status', 'vacant')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $properties,
        ]);
    }

    /**
     * Get search suggestions
     */
    public function suggestions(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $suggestions = collect();

        // City suggestions
        $cities = Property::where('published', true)
            ->where('city', 'like', "%{$query}%")
            ->distinct()
            ->pluck('city')
            ->take(5)
            ->map(function ($city) {
                return [
                    'type' => 'city',
                    'text' => $city,
                    'value' => $city
                ];
            });

        // Locality suggestions
        $localities = Property::where('published', true)
            ->where('locality', 'like', "%{$query}%")
            ->distinct()
            ->pluck('locality')
            ->take(5)
            ->map(function ($locality) {
                return [
                    'type' => 'locality',
                    'text' => $locality,
                    'value' => $locality
                ];
            });

        // Property title suggestions
        $titles = Property::where('published', true)
            ->where('title', 'like', "%{$query}%")
            ->distinct()
            ->pluck('title')
            ->take(5)
            ->map(function ($title) {
                return [
                    'type' => 'property',
                    'text' => $title,
                    'value' => $title
                ];
            });

        $suggestions = $suggestions
            ->merge($cities)
            ->merge($localities)
            ->merge($titles)
            ->take(10);

        return response()->json([
            'success' => true,
            'data' => $suggestions,
        ]);
    }

    /**
     * Get property statistics for marketplace
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_properties' => Property::where('published', true)->count(),
            'total_cities' => Property::where('published', true)->distinct('city')->count(),
            'total_organizations' => Organization::where('status', 'active')->count(),
            'featured_properties' => Property::where('published', true)->where('featured', true)->count(),
            'rent_properties' => Property::where('published', true)->where('property_type', 'rent')->count(),
            'sale_properties' => Property::where('published', true)->where('property_type', 'sale')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
