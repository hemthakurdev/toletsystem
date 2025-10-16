<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Carbon\Carbon;

class BackupService
{
    /**
     * Create database backup
     */
    public function createDatabaseBackup(string $type = 'full'): array
    {
        try {
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "database_backup_{$type}_{$timestamp}.sql";
            $backupPath = storage_path("app/backups/database/{$filename}");
            
            // Ensure backup directory exists
            $this->ensureBackupDirectory('database');
            
            // Get database configuration
            $config = config('database.connections.mysql');
            
            // Create mysqldump command
            $command = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
                $config['host'],
                $config['port'],
                $config['username'],
                $config['password'],
                $config['database'],
                $backupPath
            );
            
            // Execute backup command
            $result = Process::run($command);
            
            if ($result->successful()) {
                $fileSize = filesize($backupPath);
                
                // Compress backup
                $compressedPath = $this->compressBackup($backupPath);
                
                // Store backup metadata
                $metadata = [
                    'filename' => $filename,
                    'type' => $type,
                    'size' => $fileSize,
                    'compressed_size' => filesize($compressedPath),
                    'created_at' => now()->toISOString(),
                    'status' => 'success',
                ];
                
                $this->storeBackupMetadata('database', $metadata);
                
                Log::info("Database backup created successfully: {$filename}");
                
                return [
                    'success' => true,
                    'filename' => $filename,
                    'path' => $compressedPath,
                    'size' => $fileSize,
                    'compressed_size' => filesize($compressedPath),
                    'metadata' => $metadata,
                ];
            } else {
                throw new \Exception("Backup command failed: " . $result->errorOutput());
            }
            
        } catch (\Exception $e) {
            Log::error("Database backup failed: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create file system backup
     */
    public function createFileSystemBackup(array $directories = []): array
    {
        try {
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "files_backup_{$timestamp}.tar.gz";
            $backupPath = storage_path("app/backups/files/{$filename}");
            
            // Ensure backup directory exists
            $this->ensureBackupDirectory('files');
            
            // Default directories to backup
            $defaultDirectories = [
                storage_path('app/public'),
                storage_path('app/private'),
                public_path('uploads'),
            ];
            
            $directoriesToBackup = !empty($directories) ? $directories : $defaultDirectories;
            
            // Create tar command
            $command = sprintf(
                'tar -czf %s %s',
                $backupPath,
                implode(' ', array_map('escapeshellarg', $directoriesToBackup))
            );
            
            // Execute backup command
            $result = Process::run($command);
            
            if ($result->successful()) {
                $fileSize = filesize($backupPath);
                
                // Store backup metadata
                $metadata = [
                    'filename' => $filename,
                    'type' => 'files',
                    'size' => $fileSize,
                    'directories' => $directoriesToBackup,
                    'created_at' => now()->toISOString(),
                    'status' => 'success',
                ];
                
                $this->storeBackupMetadata('files', $metadata);
                
                Log::info("File system backup created successfully: {$filename}");
                
                return [
                    'success' => true,
                    'filename' => $filename,
                    'path' => $backupPath,
                    'size' => $fileSize,
                    'metadata' => $metadata,
                ];
            } else {
                throw new \Exception("File backup command failed: " . $result->errorOutput());
            }
            
        } catch (\Exception $e) {
            Log::error("File system backup failed: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create complete system backup
     */
    public function createCompleteBackup(): array
    {
        $results = [];
        
        // Create database backup
        $results['database'] = $this->createDatabaseBackup('complete');
        
        // Create file system backup
        $results['files'] = $this->createFileSystemBackup();
        
        // Create configuration backup
        $results['config'] = $this->createConfigurationBackup();
        
        // Store complete backup metadata
        $metadata = [
            'type' => 'complete',
            'created_at' => now()->toISOString(),
            'components' => $results,
            'status' => 'success',
        ];
        
        $this->storeBackupMetadata('complete', $metadata);
        
        return [
            'success' => true,
            'results' => $results,
            'metadata' => $metadata,
        ];
    }

    /**
     * Create configuration backup
     */
    public function createConfigurationBackup(): array
    {
        try {
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "config_backup_{$timestamp}.json";
            $backupPath = storage_path("app/backups/config/{$filename}");
            
            // Ensure backup directory exists
            $this->ensureBackupDirectory('config');
            
            // Collect configuration data
            $config = [
                'app' => config('app'),
                'database' => config('database'),
                'mail' => config('mail'),
                'cache' => config('cache'),
                'queue' => config('queue'),
                'filesystems' => config('filesystems'),
                'services' => config('services'),
                'backup_timestamp' => now()->toISOString(),
            ];
            
            // Remove sensitive data
            $config = $this->sanitizeConfigData($config);
            
            // Save configuration
            file_put_contents($backupPath, json_encode($config, JSON_PRETTY_PRINT));
            
            $fileSize = filesize($backupPath);
            
            // Store backup metadata
            $metadata = [
                'filename' => $filename,
                'type' => 'config',
                'size' => $fileSize,
                'created_at' => now()->toISOString(),
                'status' => 'success',
            ];
            
            $this->storeBackupMetadata('config', $metadata);
            
            Log::info("Configuration backup created successfully: {$filename}");
            
            return [
                'success' => true,
                'filename' => $filename,
                'path' => $backupPath,
                'size' => $fileSize,
                'metadata' => $metadata,
            ];
            
        } catch (\Exception $e) {
            Log::error("Configuration backup failed: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Restore database from backup
     */
    public function restoreDatabaseBackup(string $backupPath): array
    {
        try {
            // Check if backup file exists
            if (!file_exists($backupPath)) {
                throw new \Exception("Backup file not found: {$backupPath}");
            }
            
            // Get database configuration
            $config = config('database.connections.mysql');
            
            // Create mysql restore command
            $command = sprintf(
                'mysql --host=%s --port=%s --user=%s --password=%s %s < %s',
                $config['host'],
                $config['port'],
                $config['username'],
                $config['password'],
                $config['database'],
                $backupPath
            );
            
            // Execute restore command
            $result = Process::run($command);
            
            if ($result->successful()) {
                Log::info("Database restored successfully from: {$backupPath}");
                
                return [
                    'success' => true,
                    'message' => 'Database restored successfully',
                ];
            } else {
                throw new \Exception("Restore command failed: " . $result->errorOutput());
            }
            
        } catch (\Exception $e) {
            Log::error("Database restore failed: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * List available backups
     */
    public function listBackups(string $type = null): array
    {
        $backups = [];
        
        $types = $type ? [$type] : ['database', 'files', 'config', 'complete'];
        
        foreach ($types as $backupType) {
            $metadataPath = storage_path("app/backups/{$backupType}/metadata.json");
            
            if (file_exists($metadataPath)) {
                $metadata = json_decode(file_get_contents($metadataPath), true);
                $backups[$backupType] = $metadata;
            }
        }
        
        return $backups;
    }

    /**
     * Clean up old backups
     */
    public function cleanupOldBackups(int $retentionDays = 30): array
    {
        $cleaned = [];
        $cutoffDate = now()->subDays($retentionDays);
        
        $backupTypes = ['database', 'files', 'config', 'complete'];
        
        foreach ($backupTypes as $type) {
            $backupDir = storage_path("app/backups/{$type}");
            
            if (is_dir($backupDir)) {
                $files = glob($backupDir . '/*');
                
                foreach ($files as $file) {
                    if (is_file($file) && filemtime($file) < $cutoffDate->timestamp) {
                        if (unlink($file)) {
                            $cleaned[] = basename($file);
                            Log::info("Cleaned up old backup: " . basename($file));
                        }
                    }
                }
            }
        }
        
        return [
            'success' => true,
            'cleaned_files' => $cleaned,
            'count' => count($cleaned),
        ];
    }

    /**
     * Schedule automatic backups
     */
    public function scheduleAutomaticBackups(): void
    {
        // This would integrate with Laravel's task scheduler
        // For now, we'll store the schedule configuration
        
        $schedule = [
            'database' => [
                'frequency' => 'daily',
                'time' => '02:00',
                'retention_days' => 30,
            ],
            'files' => [
                'frequency' => 'weekly',
                'day' => 'sunday',
                'time' => '03:00',
                'retention_days' => 90,
            ],
            'complete' => [
                'frequency' => 'monthly',
                'day' => 1,
                'time' => '01:00',
                'retention_days' => 365,
            ],
        ];
        
        Storage::disk('local')->put('backup_schedule.json', json_encode($schedule, JSON_PRETTY_PRINT));
        
        Log::info("Automatic backup schedule configured");
    }

    /**
     * Get backup statistics
     */
    public function getBackupStatistics(): array
    {
        $stats = [
            'total_backups' => 0,
            'total_size' => 0,
            'last_backup' => null,
            'backup_types' => [],
        ];
        
        $backupTypes = ['database', 'files', 'config', 'complete'];
        
        foreach ($backupTypes as $type) {
            $backupDir = storage_path("app/backups/{$type}");
            
            if (is_dir($backupDir)) {
                $files = glob($backupDir . '/*');
                $typeStats = [
                    'count' => count($files),
                    'size' => 0,
                    'last_backup' => null,
                ];
                
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $typeStats['size'] += filesize($file);
                        
                        $fileTime = filemtime($file);
                        if (!$typeStats['last_backup'] || $fileTime > $typeStats['last_backup']) {
                            $typeStats['last_backup'] = $fileTime;
                        }
                    }
                }
                
                $stats['backup_types'][$type] = $typeStats;
                $stats['total_backups'] += $typeStats['count'];
                $stats['total_size'] += $typeStats['size'];
                
                if (!$stats['last_backup'] || $typeStats['last_backup'] > $stats['last_backup']) {
                    $stats['last_backup'] = $typeStats['last_backup'];
                }
            }
        }
        
        return $stats;
    }

    /**
     * Ensure backup directory exists
     */
    private function ensureBackupDirectory(string $type): void
    {
        $backupDir = storage_path("app/backups/{$type}");
        
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
    }

    /**
     * Compress backup file
     */
    private function compressBackup(string $backupPath): string
    {
        $compressedPath = $backupPath . '.gz';
        
        $command = "gzip -c {$backupPath} > {$compressedPath}";
        Process::run($command);
        
        // Remove original file
        unlink($backupPath);
        
        return $compressedPath;
    }

    /**
     * Store backup metadata
     */
    private function storeBackupMetadata(string $type, array $metadata): void
    {
        $metadataPath = storage_path("app/backups/{$type}/metadata.json");
        
        $existingMetadata = [];
        if (file_exists($metadataPath)) {
            $existingMetadata = json_decode(file_get_contents($metadataPath), true) ?? [];
        }
        
        $existingMetadata[] = $metadata;
        
        file_put_contents($metadataPath, json_encode($existingMetadata, JSON_PRETTY_PRINT));
    }

    /**
     * Sanitize configuration data
     */
    private function sanitizeConfigData(array $config): array
    {
        $sensitiveKeys = ['password', 'secret', 'key', 'token'];
        
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $config[$key] = $this->sanitizeConfigData($value);
            } elseif (in_array(strtolower($key), $sensitiveKeys)) {
                $config[$key] = '***REDACTED***';
            }
        }
        
        return $config;
    }
}
