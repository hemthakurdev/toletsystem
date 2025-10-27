<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Property;

class PropertiesController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Check if user is a frontend user
        if ($user->user_type === 'frontend') {
            return redirect()->route('marketplace.search');
        }
        
        // For organization users, check if they have an organization
        if (!$user->organization) {
            abort(403, 'You must be part of an organization to access this page.');
        }
        
        $organization = $user->organization;

        // Get properties for the organization
        $properties = $organization->properties()
            ->with(['tenants'])
            ->latest()
            ->get()
            ->map(function ($property) {
                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'short_description' => $property->short_description,
                    'price' => $property->price,
                    'city' => $property->city,
                    'locality' => $property->locality,
                    'bedrooms' => $property->bedrooms,
                    'area_sqft' => $property->area_sqft,
                    'furnished_status' => $property->furnished_status,
                    'availability_status' => $property->availability_status,
                    'published' => $property->published,
                    'is_published' => $property->published,
                    'images' => $property->images,
                    'created_at' => $property->created_at,
                    'updated_at' => $property->updated_at,
                ];
            });

        return Inertia::render('Properties/Index', [
            'properties' => $properties,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Properties/Create');
    }

    public function show($id)
    {
        $user = Auth::user();
        
        // Check if user is a frontend user
        if ($user->user_type === 'frontend') {
            return redirect()->route('marketplace.property.show', ['id' => $id]);
        }
        
        // For organization users, check if they have an organization
        if (!$user->organization) {
            abort(403, 'You must be part of an organization to access this page.');
        }
        
        $organization = $user->organization;

        $property = $organization->properties()
            ->with(['tenants', 'invoices', 'leads'])
            ->findOrFail($id);

        return Inertia::render('Properties/Show', [
            'property' => $property,
        ]);
    }

    public function edit($id): Response
    {
        $user = Auth::user();
        
        // Check if user is a frontend user
        if ($user->user_type === 'frontend') {
            abort(403, 'Frontend users cannot edit properties.');
        }
        
        // For organization users, check if they have an organization
        if (!$user->organization) {
            abort(403, 'You must be part of an organization to access this page.');
        }
        
        $organization = $user->organization;

        $property = $organization->properties()->findOrFail($id);

        return Inertia::render('Properties/Edit', [
            'property' => $property,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        // Check if user is a frontend user
        if ($user->user_type === 'frontend') {
            abort(403, 'Frontend users cannot update properties.');
        }
        
        // For organization users, check if they have an organization
        if (!$user->organization) {
            abort(403, 'You must be part of an organization to access this page.');
        }
        
        $organization = $user->organization;

        $property = $organization->properties()->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'property_type' => 'required|in:rent,sale,pg,commercial',
            'category' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'deposit_terms' => 'nullable|string',
            'city' => 'required|string|max:255',
            'locality' => 'required|string|max:255',
            'pincode' => 'required|string|max:10',
            'address_line' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'furnished_status' => 'required|in:furnished,semi_furnished,unfurnished',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'area_sqft' => 'nullable|integer|min:0',
            'availability_status' => 'required|in:vacant,occupied,maintenance,blocked',
            'published' => 'boolean',
            'featured' => 'boolean',
            'amenities' => 'nullable|array',
            'images' => 'nullable|array',
        ]);

        $validated['published'] = $validated['published'] ?? false;
        $validated['featured'] = $validated['featured'] ?? false;

        $property->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Property updated successfully',
            'data' => $property
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        
        // Check if user is a frontend user
        if ($user->user_type === 'frontend') {
            abort(403, 'Frontend users cannot delete properties.');
        }
        
        // For organization users, check if they have an organization
        if (!$user->organization) {
            abort(403, 'You must be part of an organization to access this page.');
        }
        
        $organization = $user->organization;

        $property = $organization->properties()->findOrFail($id);
        $property->delete();

        return response()->json([
            'success' => true,
            'message' => 'Property deleted successfully'
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Check if user is a frontend user
        if ($user->user_type === 'frontend') {
            abort(403, 'Frontend users cannot create properties.');
        }
        
        // For organization users, check if they have an organization
        if (!$user->organization) {
            abort(403, 'You must be part of an organization to access this page.');
        }
        
        $organization = $user->organization;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'property_type' => 'required|in:rent,sale,pg,commercial',
            'category' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'deposit_terms' => 'nullable|string',
            'city' => 'required|string|max:255',
            'locality' => 'required|string|max:255',
            'pincode' => 'required|string|max:10',
            'address_line' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'furnished_status' => 'required|in:furnished,semi_furnished,unfurnished',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'area_sqft' => 'nullable|integer|min:0',
            'availability_status' => 'required|in:vacant,occupied,maintenance,blocked',
            'published' => 'boolean',
            'featured' => 'boolean',
            'amenities' => 'nullable|array',
            'images' => 'nullable|array',
        ]);

        $validated['org_id'] = $organization->id;
        $validated['published'] = $validated['published'] ?? false;
        $validated['featured'] = $validated['featured'] ?? false;

        $property = Property::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Property created successfully',
            'data' => $property
        ], 201);
    }
}
