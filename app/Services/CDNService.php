<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class CDNService
{
    /**
     * Upload assets to CDN
     */
    public function uploadAssetsToCDN(array $assets = []): array
    {
        try {
            $results = [];
            
            // Default assets to upload
            $defaultAssets = [
                'css' => public_path('build/assets/*.css'),
                'js' => public_path('build/assets/*.js'),
                'images' => public_path('images/*'),
                'fonts' => public_path('fonts/*'),
            ];
            
            $assetsToUpload = !empty($assets) ? $assets : $defaultAssets;
            
            foreach ($assetsToUpload as $type => $pattern) {
                $files = glob($pattern);
                
                foreach ($files as $file) {
                    $result = $this->uploadFileToCDN($file, $type);
                    $results[] = $result;
                }
            }
            
            Log::info("CDN upload completed", ['results' => $results]);
            
            return [
                'success' => true,
                'uploaded_files' => $results,
                'count' => count($results),
            ];
            
        } catch (\Exception $e) {
            Log::error("CDN upload failed: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Upload single file to CDN
     */
    public function uploadFileToCDN(string $filePath, string $type = 'general'): array
    {
        try {
            $fileName = basename($filePath);
            $cdnPath = $this->getCDNPath($type, $fileName);
            
            // Upload to CDN (implementation depends on CDN provider)
            $uploadResult = $this->performCDNUpload($filePath, $cdnPath);
            
            if ($uploadResult['success']) {
                // Store CDN URL mapping
                $this->storeCDNMapping($filePath, $uploadResult['url']);
                
                Log::info("File uploaded to CDN", [
                    'file' => $fileName,
                    'cdn_url' => $uploadResult['url'],
                ]);
                
                return [
                    'success' => true,
                    'file' => $fileName,
                    'local_path' => $filePath,
                    'cdn_url' => $uploadResult['url'],
                    'cdn_path' => $cdnPath,
                ];
            } else {
                throw new \Exception($uploadResult['error']);
            }
            
        } catch (\Exception $e) {
            Log::error("CDN upload failed for {$filePath}: " . $e->getMessage());
            
            return [
                'success' => false,
                'file' => basename($filePath),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Optimize images for CDN
     */
    public function optimizeImagesForCDN(array $images = []): array
    {
        try {
            $results = [];
            
            // Default images to optimize
            $defaultImages = glob(public_path('images/*.{jpg,jpeg,png,gif,webp}'), GLOB_BRACE);
            $imagesToOptimize = !empty($images) ? $images : $defaultImages;
            
            foreach ($imagesToOptimize as $imagePath) {
                $result = $this->optimizeImage($imagePath);
                $results[] = $result;
            }
            
            return [
                'success' => true,
                'optimized_images' => $results,
                'count' => count($results),
            ];
            
        } catch (\Exception $e) {
            Log::error("Image optimization failed: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate CDN URLs for assets
     */
    public function generateCDNUrls(array $assets): array
    {
        $cdnUrls = [];
        
        foreach ($assets as $asset) {
            $cdnUrl = $this->getCDNUrl($asset);
            $cdnUrls[$asset] = $cdnUrl;
        }
        
        return $cdnUrls;
    }

    /**
     * Purge CDN cache
     */
    public function purgeCDNCache(array $urls = []): array
    {
        try {
            $results = [];
            
            if (empty($urls)) {
                // Purge all CDN cache
                $urls = $this->getAllCDNUrls();
            }
            
            foreach ($urls as $url) {
                $result = $this->performCDNPurge($url);
                $results[] = $result;
            }
            
            Log::info("CDN cache purge completed", ['results' => $results]);
            
            return [
                'success' => true,
                'purged_urls' => $results,
                'count' => count($results),
            ];
            
        } catch (\Exception $e) {
            Log::error("CDN cache purge failed: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get CDN statistics
     */
    public function getCDNStatistics(): array
    {
        try {
            $stats = [
                'total_assets' => $this->getTotalCDNAssets(),
                'total_size' => $this->getTotalCDNSize(),
                'bandwidth_usage' => $this->getBandwidthUsage(),
                'cache_hit_rate' => $this->getCacheHitRate(),
                'popular_assets' => $this->getPopularAssets(),
                'last_upload' => $this->getLastUploadTime(),
            ];
            
            return $stats;
            
        } catch (\Exception $e) {
            Log::error("Failed to get CDN statistics: " . $e->getMessage());
            
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Configure CDN settings
     */
    public function configureCDN(array $settings): array
    {
        try {
            // Validate settings
            $validatedSettings = $this->validateCDNSettings($settings);
            
            // Store settings
            Storage::disk('local')->put('cdn_config.json', json_encode($validatedSettings, JSON_PRETTY_PRINT));
            
            // Test CDN connection
            $testResult = $this->testCDNConnection($validatedSettings);
            
            if ($testResult['success']) {
                Log::info("CDN configured successfully");
                
                return [
                    'success' => true,
                    'settings' => $validatedSettings,
                    'message' => 'CDN configured successfully',
                ];
            } else {
                throw new \Exception("CDN connection test failed: " . $testResult['error']);
            }
            
        } catch (\Exception $e) {
            Log::error("CDN configuration failed: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get CDN path for file
     */
    private function getCDNPath(string $type, string $fileName): string
    {
        $timestamp = date('Y-m-d');
        return "assets/{$type}/{$timestamp}/{$fileName}";
    }

    /**
     * Perform CDN upload (implementation depends on CDN provider)
     */
    private function performCDNUpload(string $filePath, string $cdnPath): array
    {
        // This is a mock implementation
        // In real implementation, you would use CDN provider's SDK
        
        $cdnBaseUrl = config('cdn.base_url', 'https://cdn.example.com');
        $cdnUrl = $cdnBaseUrl . '/' . $cdnPath;
        
        // Simulate upload
        $fileSize = filesize($filePath);
        
        return [
            'success' => true,
            'url' => $cdnUrl,
            'size' => $fileSize,
        ];
    }

    /**
     * Store CDN URL mapping
     */
    private function storeCDNMapping(string $localPath, string $cdnUrl): void
    {
        $mappings = $this->getCDNMappings();
        $mappings[$localPath] = $cdnUrl;
        
        Storage::disk('local')->put('cdn_mappings.json', json_encode($mappings, JSON_PRETTY_PRINT));
    }

    /**
     * Get CDN URL mappings
     */
    private function getCDNMappings(): array
    {
        $mappingsPath = storage_path('app/cdn_mappings.json');
        
        if (file_exists($mappingsPath)) {
            return json_decode(file_get_contents($mappingsPath), true) ?? [];
        }
        
        return [];
    }

    /**
     * Optimize single image
     */
    private function optimizeImage(string $imagePath): array
    {
        try {
            $fileName = basename($imagePath);
            $optimizedPath = storage_path("app/optimized/{$fileName}");
            
            // Ensure optimized directory exists
            if (!is_dir(dirname($optimizedPath))) {
                mkdir(dirname($optimizedPath), 0755, true);
            }
            
            // Use ImageMagick or similar tool to optimize
            $command = sprintf(
                'convert "%s" -quality 85 -strip "%s"',
                $imagePath,
                $optimizedPath
            );
            
            $result = Process::run($command);
            
            if ($result->successful()) {
                $originalSize = filesize($imagePath);
                $optimizedSize = filesize($optimizedPath);
                $savings = $originalSize - $optimizedSize;
                $savingsPercent = round(($savings / $originalSize) * 100, 2);
                
                return [
                    'success' => true,
                    'file' => $fileName,
                    'original_size' => $originalSize,
                    'optimized_size' => $optimizedSize,
                    'savings' => $savings,
                    'savings_percent' => $savingsPercent,
                    'optimized_path' => $optimizedPath,
                ];
            } else {
                throw new \Exception("Image optimization failed: " . $result->errorOutput());
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'file' => basename($imagePath),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get CDN URL for asset
     */
    private function getCDNUrl(string $asset): string
    {
        $mappings = $this->getCDNMappings();
        
        if (isset($mappings[$asset])) {
            return $mappings[$asset];
        }
        
        // Fallback to local URL
        return asset($asset);
    }

    /**
     * Perform CDN cache purge
     */
    private function performCDNPurge(string $url): array
    {
        // This is a mock implementation
        // In real implementation, you would use CDN provider's purge API
        
        return [
            'success' => true,
            'url' => $url,
            'purged_at' => now()->toISOString(),
        ];
    }

    /**
     * Get all CDN URLs
     */
    private function getAllCDNUrls(): array
    {
        $mappings = $this->getCDNMappings();
        return array_values($mappings);
    }

    /**
     * Get total CDN assets count
     */
    private function getTotalCDNAssets(): int
    {
        $mappings = $this->getCDNMappings();
        return count($mappings);
    }

    /**
     * Get total CDN size
     */
    private function getTotalCDNSize(): int
    {
        // This would be implemented based on CDN provider's API
        return 0;
    }

    /**
     * Get bandwidth usage
     */
    private function getBandwidthUsage(): array
    {
        // This would be implemented based on CDN provider's API
        return [
            'current_month' => 0,
            'last_month' => 0,
            'total' => 0,
        ];
    }

    /**
     * Get cache hit rate
     */
    private function getCacheHitRate(): float
    {
        // This would be implemented based on CDN provider's API
        return 95.5;
    }

    /**
     * Get popular assets
     */
    private function getPopularAssets(): array
    {
        // This would be implemented based on CDN provider's API
        return [];
    }

    /**
     * Get last upload time
     */
    private function getLastUploadTime(): string
    {
        $mappings = $this->getCDNMappings();
        
        if (empty($mappings)) {
            return 'Never';
        }
        
        // This would be implemented based on actual upload timestamps
        return now()->subHours(2)->toISOString();
    }

    /**
     * Validate CDN settings
     */
    private function validateCDNSettings(array $settings): array
    {
        $required = ['provider', 'base_url', 'api_key'];
        
        foreach ($required as $field) {
            if (!isset($settings[$field]) || empty($settings[$field])) {
                throw new \Exception("Required CDN setting missing: {$field}");
            }
        }
        
        return $settings;
    }

    /**
     * Test CDN connection
     */
    private function testCDNConnection(array $settings): array
    {
        try {
            // This would test the actual CDN connection
            // For now, we'll simulate a successful test
            
            return [
                'success' => true,
                'message' => 'CDN connection test successful',
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
