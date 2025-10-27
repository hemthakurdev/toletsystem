<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class FavoritesController extends Controller
{
    /**
     * Get user's favorite properties
     */
    public function index(Request $request): JsonResponse
    {
        // Try to get user from Sanctum token first, then from session
        $user = $request->user() ?? Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        $favorites = $user->favoriteProperties()
            ->with(['organization', 'media'])
            ->withCount('favorites')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => [
                'favorites' => $favorites->items(),
                'pagination' => [
                    'current_page' => $favorites->currentPage(),
                    'last_page' => $favorites->lastPage(),
                    'per_page' => $favorites->perPage(),
                    'total' => $favorites->total(),
                ]
            ]
        ]);
    }

    /**
     * Add a property to favorites
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id'
        ]);

        $user = $request->user() ?? Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        $propertyId = $request->property_id;

        // Check if already favorited
        $existingFavorite = Favorite::where('user_id', $user->id)
            ->where('property_id', $propertyId)
            ->first();

        if ($existingFavorite) {
            return response()->json([
                'success' => false,
                'message' => 'Property is already in your favorites'
            ], 400);
        }

        // Create new favorite
        Favorite::create([
            'user_id' => $user->id,
            'property_id' => $propertyId
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Property added to favorites successfully'
        ]);
    }

    /**
     * Remove a property from favorites
     */
    public function destroy(Request $request, $propertyId): JsonResponse
    {
        $user = $request->user() ?? Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $favorite = Favorite::where('user_id', $user->id)
            ->where('property_id', $propertyId)
            ->first();

        if (!$favorite) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found in favorites'
            ], 404);
        }

        $favorite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Property removed from favorites successfully'
        ]);
    }

    /**
     * Toggle favorite status for a property
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id'
        ]);

        $user = $request->user() ?? Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        $propertyId = $request->property_id;

        $favorite = Favorite::where('user_id', $user->id)
            ->where('property_id', $propertyId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $isFavorited = false;
            $message = 'Property removed from favorites';
        } else {
            Favorite::create([
                'user_id' => $user->id,
                'property_id' => $propertyId
            ]);
            $isFavorited = true;
            $message = 'Property added to favorites';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'is_favorited' => $isFavorited
            ]
        ]);
    }

    /**
     * Check if a property is favorited by the user
     */
    public function check(Request $request, $propertyId): JsonResponse
    {
        $user = $request->user() ?? Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $isFavorited = Favorite::where('user_id', $user->id)
            ->where('property_id', $propertyId)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'is_favorited' => $isFavorited
            ]
        ]);
    }

    /**
     * Get favorites count for user
     */
    public function count(): JsonResponse
    {
        $user = $request->user() ?? Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        $count = $user->favorites()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count
            ]
        ]);
    }
}