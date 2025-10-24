<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{
    /**
     * Display a listing of properties
     */
    public function index(Request $request): JsonResponse
    {
        // Check if user is authenticated for organization properties
        if (auth()->check() && $request->routeIs('org.*')) {
            // Organization properties (authenticated)
            $query = Property::with(['organization', 'amenities', 'tenants'])
                ->where('org_id', auth()->user()->org_id);
        } else {
            // Public marketplace properties
            $query = Property::published()->with(['organization', 'amenities']);
        }

        // Apply filters
        if ($request->has('city')) {
            $query->byCity($request->city);
        }

        if ($request->has('locality')) {
            $query->byLocality($request->locality);
        }

        if ($request->has('property_type')) {
            $query->byType($request->property_type);
        }

        if ($request->has('min_price') && $request->has('max_price')) {
            $query->byPriceRange($request->min_price, $request->max_price);
        }

        if ($request->has('bedrooms')) {
            $query->byBedrooms($request->bedrooms);
        }

        if ($request->has('furnished_status')) {
            $query->where('furnished_status', $request->furnished_status);
        }

        if ($request->has('featured')) {
            $query->featured();
        }

        if ($request->has('availability_status')) {
            $query->where('availability_status', $request->availability_status);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('locality', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $properties = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $properties,
        ]);
    }

    /**
     * Store a newly created property
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'long_description' => 'nullable|string',
            'property_type' => 'required|in:rent,sale,pg,commercial',
            'category' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'deposit_terms' => 'nullable|string',
            'city' => 'required|string|max:100',
            'locality' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'address_line' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'furnished_status' => 'required|in:furnished,semi_furnished,unfurnished',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'area_sqft' => 'nullable|integer|min:0',
            'amenities' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $property = Property::create([
            'org_id' => auth()->user()->org_id,
            'title' => $request->title,
            'short_description' => $request->short_description,
            'long_description' => $request->long_description,
            'property_type' => $request->property_type,
            'category' => $request->category,
            'price' => $request->price,
            'security_deposit' => $request->security_deposit,
            'deposit_terms' => $request->deposit_terms,
            'city' => $request->city,
            'locality' => $request->locality,
            'pincode' => $request->pincode,
            'address_line' => $request->address_line,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'furnished_status' => $request->furnished_status,
            'bedrooms' => $request->bedrooms,
            'bathrooms' => $request->bathrooms,
            'area_sqft' => $request->area_sqft,
            'amenities' => $request->amenities,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Property created successfully',
            'data' => $property->load('organization'),
        ], 201);
    }

    /**
     * Display the specified property
     */
    public function show($id): JsonResponse
    {
        $property = Property::find($id);
        
        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'The property you\'re looking for doesn\'t exist or has been removed.',
            ], 404);
        }

        // Check if property is published for public access
        if (!$property->published) {
            return response()->json([
                'success' => false,
                'message' => 'The property you\'re looking for doesn\'t exist or has been removed.',
            ], 404);
        }

        $property->load(['organization', 'amenities']);

        return response()->json([
            'success' => true,
            'data' => $property,
        ]);
    }

    /**
     * Update the specified property
     */
    public function update(Request $request, Property $property): JsonResponse
    {
        // Check if user owns this property
        if (auth()->check() && $property->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this property',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'long_description' => 'nullable|string',
            'property_type' => 'sometimes|required|in:rent,sale,pg,commercial',
            'category' => 'nullable|string|max:100',
            'price' => 'sometimes|required|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'deposit_terms' => 'nullable|string',
            'city' => 'sometimes|required|string|max:100',
            'locality' => 'sometimes|required|string|max:100',
            'pincode' => 'sometimes|required|string|max:10',
            'address_line' => 'sometimes|required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'furnished_status' => 'sometimes|required|in:furnished,semi_furnished,unfurnished',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'area_sqft' => 'nullable|integer|min:0',
            'amenities' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $property->update($request->only([
            'title', 'short_description', 'long_description', 'property_type',
            'category', 'price', 'security_deposit', 'deposit_terms',
            'city', 'locality', 'pincode', 'address_line', 'latitude',
            'longitude', 'furnished_status', 'bedrooms', 'bathrooms',
            'area_sqft', 'amenities'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Property updated successfully',
            'data' => $property->load('organization'),
        ]);
    }

    /**
     * Remove the specified property
     */
    public function destroy(Property $property): JsonResponse
    {
        // Check if user owns this property
        if (auth()->check() && $property->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this property',
            ], 403);
        }

        $property->delete();

        return response()->json([
            'success' => true,
            'message' => 'Property deleted successfully',
        ]);
    }

    /**
     * Publish property to marketplace
     */
    public function publish(Property $property): JsonResponse
    {
        $property->publish();

        return response()->json([
            'success' => true,
            'message' => 'Property published successfully',
            'data' => $property,
        ]);
    }

    /**
     * Unpublish property from marketplace
     */
    public function unpublish(Property $property): JsonResponse
    {
        $property->unpublish();

        return response()->json([
            'success' => true,
            'message' => 'Property unpublished successfully',
            'data' => $property,
        ]);
    }

    /**
     * Contact property owner (public)
     */
    public function contact(Request $request, Property $property): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $lead = $property->leads()->create([
            'org_id' => $property->org_id,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'message' => $request->message,
            'source' => 'public_listing',
        ]);

        // Calculate lead score
        $lead->calculateLeadScore();

        return response()->json([
            'success' => true,
            'message' => 'Your inquiry has been sent successfully',
            'data' => $lead,
        ]);
    }
}