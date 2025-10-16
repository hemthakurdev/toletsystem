<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class FileUploadService
{
    protected $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Upload property images with thumbnails
     */
    public function uploadPropertyImages(array $files, int $propertyId): array
    {
        $uploadedImages = [];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $uploadedImages[] = $this->uploadPropertyImage($file, $propertyId);
            }
        }
        
        return $uploadedImages;
    }

    /**
     * Upload single property image with thumbnails
     */
    public function uploadPropertyImage(UploadedFile $file, int $propertyId): array
    {
        // Validate file
        $this->validateImage($file);
        
        // Generate unique filename
        $filename = $this->generateFilename($file);
        $directory = "properties/{$propertyId}";
        
        // Create directories
        $this->createDirectories($directory);
        
        // Process and save original image
        $originalPath = $this->saveOriginalImage($file, $directory, $filename);
        
        // Generate thumbnails
        $thumbnails = $this->generateThumbnails($file, $directory, $filename);
        
        return [
            'original' => $originalPath,
            'thumbnails' => $thumbnails,
            'filename' => $filename,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    /**
     * Validate uploaded image
     */
    protected function validateImage(UploadedFile $file): void
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 10 * 1024 * 1024; // 10MB
        
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \InvalidArgumentException('Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.');
        }
        
        if ($file->getSize() > $maxSize) {
            throw new \InvalidArgumentException('File size too large. Maximum size is 10MB.');
        }
    }

    /**
     * Generate unique filename
     */
    protected function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $random = Str::random(8);
        
        return "{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Create necessary directories
     */
    protected function createDirectories(string $directory): void
    {
        $directories = [
            $directory,
            "{$directory}/thumbnails",
            "{$directory}/thumbnails/small",
            "{$directory}/thumbnails/medium",
            "{$directory}/thumbnails/large",
        ];
        
        foreach ($directories as $dir) {
            if (!Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->makeDirectory($dir);
            }
        }
    }

    /**
     * Save original image
     */
    protected function saveOriginalImage(UploadedFile $file, string $directory, string $filename): string
    {
        $path = "{$directory}/{$filename}";
        
        // Resize if too large (max 1920px width)
        $image = $this->imageManager->read($file->getPathname());
        
        if ($image->width() > 1920) {
            $image->scaleDown(width: 1920);
        }
        
        // Save with quality optimization
        $image->toJpeg(90)->save(storage_path("app/public/{$path}"));
        
        return $path;
    }

    /**
     * Generate thumbnails in different sizes
     */
    protected function generateThumbnails(UploadedFile $file, string $directory, string $filename): array
    {
        $thumbnails = [];
        $sizes = [
            'small' => [300, 200],
            'medium' => [600, 400],
            'large' => [1200, 800],
        ];
        
        $image = $this->imageManager->read($file->getPathname());
        
        foreach ($sizes as $size => $dimensions) {
            [$width, $height] = $dimensions;
            
            $thumbnail = clone $image;
            $thumbnail->cover($width, $height);
            
            $thumbnailPath = "{$directory}/thumbnails/{$size}/{$filename}";
            $thumbnail->toJpeg(85)->save(storage_path("app/public/{$thumbnailPath}"));
            
            $thumbnails[$size] = $thumbnailPath;
        }
        
        return $thumbnails;
    }

    /**
     * Delete property images
     */
    public function deletePropertyImages(int $propertyId, array $imagePaths = []): bool
    {
        $directory = "properties/{$propertyId}";
        
        if (empty($imagePaths)) {
            // Delete entire directory
            return Storage::disk('public')->deleteDirectory($directory);
        }
        
        // Delete specific images
        $deleted = true;
        foreach ($imagePaths as $imagePath) {
            if (!Storage::disk('public')->delete($imagePath)) {
                $deleted = false;
            }
            
            // Delete thumbnails
            $this->deleteThumbnails($imagePath);
        }
        
        return $deleted;
    }

    /**
     * Delete thumbnails for an image
     */
    protected function deleteThumbnails(string $imagePath): void
    {
        $pathInfo = pathinfo($imagePath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['basename'];
        
        $thumbnailSizes = ['small', 'medium', 'large'];
        
        foreach ($thumbnailSizes as $size) {
            $thumbnailPath = "{$directory}/thumbnails/{$size}/{$filename}";
            Storage::disk('public')->delete($thumbnailPath);
        }
    }

    /**
     * Get image URL
     */
    public function getImageUrl(string $path): string
    {
        return Storage::disk('public')->url($path);
    }

    /**
     * Get thumbnail URL
     */
    public function getThumbnailUrl(string $originalPath, string $size = 'medium'): string
    {
        $pathInfo = pathinfo($originalPath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['basename'];
        
        $thumbnailPath = "{$directory}/thumbnails/{$size}/{$filename}";
        
        if (Storage::disk('public')->exists($thumbnailPath)) {
            return Storage::disk('public')->url($thumbnailPath);
        }
        
        // Fallback to original if thumbnail doesn't exist
        return $this->getImageUrl($originalPath);
    }

    /**
     * Upload document files
     */
    public function uploadDocument(UploadedFile $file, string $directory = 'documents'): array
    {
        $allowedMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png',
            'image/gif',
        ];
        
        $maxSize = 20 * 1024 * 1024; // 20MB
        
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \InvalidArgumentException('Invalid file type. Only PDF, DOC, DOCX, and image files are allowed.');
        }
        
        if ($file->getSize() > $maxSize) {
            throw new \InvalidArgumentException('File size too large. Maximum size is 20MB.');
        }
        
        $filename = $this->generateFilename($file);
        $path = "{$directory}/{$filename}";
        
        Storage::disk('public')->put($path, file_get_contents($file->getPathname()));
        
        return [
            'path' => $path,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    /**
     * Delete document
     */
    public function deleteDocument(string $path): bool
    {
        return Storage::disk('public')->delete($path);
    }
}
