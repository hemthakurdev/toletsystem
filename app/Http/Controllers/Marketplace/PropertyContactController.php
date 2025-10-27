<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Exception;
use Illuminate\Support\Facades\Schema;

class PropertyContactController extends Controller
{
    /**
     * Show the contact form for a property
     */
    public function create($property)
    {
        try {
            $property = Property::with('organization')->findOrFail($property);
            
            return Inertia::render('Marketplace/ContactProperty', [
                'property' => [
                    'id' => $property->id,
                    'title' => $property->title,
                    'price' => $property->price,
                    'locality' => $property->locality,
                    'city' => $property->city,
                    'bedrooms' => $property->bedrooms,
                    'bathrooms' => $property->bathrooms,
                    'area_sqft' => $property->area_sqft,
                    'furnished_status' => $property->furnished_status,
                    'property_type' => $property->property_type,
                    'description' => $property->description,
                    'images' => $property->images,
                    'organization' => [
                        'name' => $property->organization->name,
                        'logo' => $property->organization->logo,
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            return redirect()->route('marketplace.search')
                           ->with('error', 'Property not found.');
        }
    }

    /**
     * Store the contact form submission
     */
    public function store(Request $request, $property)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => ['required', 'string', 'max:20', 'regex:/^([0-9\s\-\+\(\)]*)$/'],
                'message' => 'required|string|min:10|max:1000'
            ], [
                'phone.regex' => 'Please enter a valid phone number',
                'message.min' => 'Your message must be at least 10 characters long',
                'message.max' => 'Your message cannot exceed 1000 characters'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find the property
            $property = Property::findOrFail($property);

            // Debug property status
            Log::info('Property status check:', [
                'property_id' => $property->id,
                'is_published' => $property->is_published ?? 'not set',
                'availability_status' => $property->availability_status ?? 'not set'
            ]);

            // Check if property is available - less strict check
            if (isset($property->is_published) && !$property->is_published) {
                return response()->json([
                    'success' => false,
                    'message' => 'This property is not published.'
                ], 400);
            }

            if (isset($property->availability_status) && $property->availability_status === 'unavailable') {
                return response()->json([
                    'success' => false,
                    'message' => 'This property is no longer available for inquiry.'
                ], 400);
            }

            // Check for duplicate leads within last hour
            $recentLead = Lead::where('property_id', $property->id)
                ->where('email', $request->email)
                ->where('created_at', '>=', now()->subHour())
                ->first();

            if ($recentLead) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already sent an inquiry for this property recently. Please wait before sending another message.'
                ], 429);
            }

            // Debug property data
            Log::info('Property data:', [
                'id' => $property->id,
                'organization_id' => $property->organization_id ?? 'not set',
                'org_id' => $property->org_id ?? 'not set'
            ]);

            try {
                // Check database connection and table existence
                try {
                    if (!Schema::hasTable('leads')) {
                        throw new \Exception('The leads table does not exist in the database');
                    }

                    // Debug table structure
                    $columns = Schema::getColumnListing('leads');
                    Log::info('Leads table structure:', ['columns' => $columns]);

                    // Load property with organization
                    $property = $property->load('organization');
                    
                    // Debug property data
                    Log::info('Property data after loading:', [
                        'property_id' => $property->id,
                        'org_data' => [
                            'org_id' => $property->org_id ?? null,
                            'organization_id' => $property->organization_id ?? null,
                            'organization' => $property->organization ? [
                                'id' => $property->organization->id ?? null,
                                'name' => $property->organization->name ?? null
                            ] : null
                        ]
                    ]);

                    // Get organization ID with detailed logging
                    $orgId = null;
                    if ($property->org_id) {
                        $orgId = $property->org_id;
                        Log::info('Using property.org_id');
                    } elseif ($property->organization_id) {
                        $orgId = $property->organization_id;
                        Log::info('Using property.organization_id');
                    } elseif ($property->organization && $property->organization->id) {
                        $orgId = $property->organization->id;
                        Log::info('Using property.organization.id');
                    }

                    if (!$orgId) {
                        throw new \Exception('Organization ID could not be determined for property ID: ' . $property->id);
                    }

                    // Create lead with minimal required data first
                    $lead = new Lead();
                    $lead->property_id = $property->id;
                    $lead->org_id = $orgId;
                    $lead->name = $request->name;
                    $lead->email = $request->email;
                    $lead->phone = $request->phone;
                    $lead->message = $request->message;
                    $lead->status = 'new';

                    // Add additional fields only if they exist in the table
                    if (in_array('source', $columns)) {
                        $lead->source = 'public_listing';
                    }
                    if (in_array('lead_score', $columns)) {
                        $lead->lead_score = 1;
                    }
                    if (in_array('contacted_at', $columns)) {
                        $lead->contacted_at = now();
                    }

                    // Log the final lead data before saving
                    Log::info('About to save lead with data:', [
                        'lead_data' => $lead->toArray(),
                        'fillable_attributes' => $lead->getFillable()
                    ]);

                    // Attempt to save and log result
                    $saved = $lead->save();
                    Log::info('Lead save result:', ['success' => $saved, 'lead_id' => $lead->id ?? null]);

                    if (!$saved) {
                        throw new \Exception('Failed to save lead record');
                    }

                    // Log successful creation
                    Log::info('New lead created:', [
                        'lead_id' => $lead->id,
                        'property_id' => $property->id,
                        'email' => $lead->email
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Thank you! Your inquiry has been sent successfully. The property owner will contact you soon.'
                    ]);

                } catch (ValidationException $e) {
                    return response()->json([
                        'success' => false,
                        'errors' => $e->errors()
                    ], 422);
                }

            } catch (\Illuminate\Database\QueryException $e) {
                Log::error('Database error in property contact form:', [
                    'property_id' => $property->id ?? 'unknown',
                    'error_message' => $e->getMessage(),
                    'sql' => $e->getSql() ?? 'unknown',
                    'bindings' => $e->getBindings() ?? [],
                    'error_code' => $e->getCode(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'A database error occurred while processing your request.',
                    'debug_info' => config('app.debug') ? [
                        'error' => $e->getMessage(),
                        'sql' => $e->getSql() ?? 'unknown'
                    ] : null
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('General error in property contact form:', [
                'property_id' => $property->id ?? 'unknown',
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Set debug mode for development
            if (app()->environment('local', 'development')) {
                config(['app.debug' => true]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request.',
                'debug_info' => config('app.debug') ? [
                    'error_type' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
