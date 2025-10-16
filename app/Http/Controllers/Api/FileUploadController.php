<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FileUploadService;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FileUploadController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Upload property images
     */
    public function uploadPropertyImages(Request $request, Property $property): JsonResponse
    {
        // Check if property belongs to user's organization
        if ($property->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to upload images for this property',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $uploadedImages = $this->fileUploadService->uploadPropertyImages(
                $request->file('images'),
                $property->id
            );

            // Update property with image paths
            $existingImages = $property->images ? json_decode($property->images, true) : [];
            $newImages = array_merge($existingImages, $uploadedImages);
            
            $property->update(['images' => json_encode($newImages)]);

            return response()->json([
                'success' => true,
                'message' => 'Images uploaded successfully',
                'data' => [
                    'uploaded_images' => $uploadedImages,
                    'total_images' => count($newImages),
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload images: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete property images
     */
    public function deletePropertyImages(Request $request, Property $property): JsonResponse
    {
        // Check if property belongs to user's organization
        if ($property->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete images for this property',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'image_paths' => 'required|array|min:1',
            'image_paths.*' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $deleted = $this->fileUploadService->deletePropertyImages(
                $property->id,
                $request->image_paths
            );

            if ($deleted) {
                // Update property images array
                $existingImages = $property->images ? json_decode($property->images, true) : [];
                $remainingImages = array_filter($existingImages, function ($image) use ($request) {
                    return !in_array($image['original'], $request->image_paths);
                });
                
                $property->update(['images' => json_encode(array_values($remainingImages))]);

                return response()->json([
                    'success' => true,
                    'message' => 'Images deleted successfully',
                    'data' => [
                        'remaining_images' => count($remainingImages),
                    ],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete some images',
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete images: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload document
     */
    public function uploadDocument(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'document' => 'required|file|mimes:pdf,doc,docx,jpeg,png,jpg,gif|max:20480', // 20MB max
            'type' => 'required|string|in:tenant_document,property_document,lease_agreement,other',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $uploadedDocument = $this->fileUploadService->uploadDocument(
                $request->file('document'),
                "documents/{$request->type}"
            );

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'data' => $uploadedDocument,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete document
     */
    public function deleteDocument(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'path' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $deleted = $this->fileUploadService->deleteDocument($request->path);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Document deleted successfully',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete document',
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get image URL
     */
    public function getImageUrl(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'path' => 'required|string',
            'size' => 'nullable|string|in:small,medium,large',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $size = $request->get('size', 'medium');
            
            if ($size === 'original') {
                $url = $this->fileUploadService->getImageUrl($request->path);
            } else {
                $url = $this->fileUploadService->getThumbnailUrl($request->path, $size);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'url' => $url,
                    'path' => $request->path,
                    'size' => $size,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get image URL: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get property images
     */
    public function getPropertyImages(Property $property): JsonResponse
    {
        $images = $property->images ? json_decode($property->images, true) : [];
        
        // Add URLs for each image
        $imagesWithUrls = array_map(function ($image) {
            return [
                'original' => $image['original'],
                'thumbnails' => $image['thumbnails'],
                'filename' => $image['filename'],
                'size' => $image['size'],
                'mime_type' => $image['mime_type'],
                'urls' => [
                    'original' => $this->fileUploadService->getImageUrl($image['original']),
                    'small' => $this->fileUploadService->getThumbnailUrl($image['original'], 'small'),
                    'medium' => $this->fileUploadService->getThumbnailUrl($image['original'], 'medium'),
                    'large' => $this->fileUploadService->getThumbnailUrl($image['original'], 'large'),
                ],
            ];
        }, $images);

        return response()->json([
            'success' => true,
            'data' => $imagesWithUrls,
        ]);
    }
}
