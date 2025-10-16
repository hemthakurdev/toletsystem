<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class MonitoringService
{
    /**
     * Log application performance metrics
     */
    public function logPerformanceMetrics(string $endpoint, float $responseTime, int $memoryUsage): void
    {
        $metrics = [
            'endpoint' => $endpoint,
            'response_time' => $responseTime,
            'memory_usage' => $memoryUsage,
            'timestamp' => now()->toISOString(),
            'memory_peak' => memory_get_peak_usage(true),
            'cpu_usage' => $this->getCpuUsage(),
        ];

        Log::channel('performance')->info('Performance metrics', $metrics);
        
        // Store in cache for real-time monitoring
        Cache::put("performance:{$endpoint}", $metrics, 300); // 5 minutes
    }

    /**
     * Log database query performance
     */
    public function logDatabasePerformance(string $query, float $executionTime, int $rowsAffected = 0): void
    {
        if ($executionTime > 1.0) { // Log slow queries (> 1 second)
            Log::channel('database')->warning('Slow query detected', [
                'query' => $query,
                'execution_time' => $executionTime,
                'rows_affected' => $rowsAffected,
                'timestamp' => now()->toISOString(),
            ]);
        }

        // Store query metrics
        $key = 'db_metrics:' . md5($query);
        $metrics = Cache::get($key, ['count' => 0, 'total_time' => 0, 'avg_time' => 0]);
        
        $metrics['count']++;
        $metrics['total_time'] += $executionTime;
        $metrics['avg_time'] = $metrics['total_time'] / $metrics['count'];
        
        Cache::put($key, $metrics, 3600); // 1 hour
    }

    /**
     * Log error with context
     */
    public function logError(\Throwable $exception, array $context = []): void
    {
        $errorData = [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'context' => $context,
        ];

        Log::channel('errors')->error('Application error', $errorData);
        
        // Store error count for monitoring
        $this->incrementErrorCount($exception->getMessage());
    }

    /**
     * Log API usage statistics
     */
    public function logApiUsage(string $endpoint, string $method, int $responseCode, float $responseTime): void
    {
        $usage = [
            'endpoint' => $endpoint,
            'method' => $method,
            'response_code' => $responseCode,
            'response_time' => $responseTime,
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
        ];

        Log::channel('api')->info('API usage', $usage);
        
        // Store API metrics
        $key = "api_metrics:{$endpoint}:{$method}";
        $metrics = Cache::get($key, ['requests' => 0, 'total_time' => 0, 'errors' => 0]);
        
        $metrics['requests']++;
        $metrics['total_time'] += $responseTime;
        
        if ($responseCode >= 400) {
            $metrics['errors']++;
        }
        
        Cache::put($key, $metrics, 3600); // 1 hour
    }

    /**
     * Get system health status
     */
    public function getSystemHealth(): array
    {
        return [
            'status' => $this->getOverallStatus(),
            'timestamp' => now()->toISOString(),
            'database' => $this->checkDatabaseHealth(),
            'cache' => $this->checkCacheHealth(),
            'storage' => $this->checkStorageHealth(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
            'errors' => $this->getErrorStats(),
            'performance' => $this->getPerformanceStats(),
        ];
    }

    /**
     * Get application metrics
     */
    public function getApplicationMetrics(): array
    {
        return [
            'users' => $this->getUserMetrics(),
            'properties' => $this->getPropertyMetrics(),
            'payments' => $this->getPaymentMetrics(),
            'api_usage' => $this->getApiUsageMetrics(),
            'errors' => $this->getErrorMetrics(),
            'performance' => $this->getPerformanceMetrics(),
        ];
    }

    /**
     * Generate monitoring report
     */
    public function generateMonitoringReport(string $period = '24h'): array
    {
        $report = [
            'period' => $period,
            'generated_at' => now()->toISOString(),
            'summary' => $this->getReportSummary($period),
            'performance' => $this->getPerformanceReport($period),
            'errors' => $this->getErrorReport($period),
            'usage' => $this->getUsageReport($period),
            'recommendations' => $this->getRecommendations(),
        ];

        // Store report
        $filename = "monitoring_report_{$period}_" . now()->format('Y-m-d_H-i-s') . '.json';
        Storage::disk('local')->put("reports/{$filename}", json_encode($report, JSON_PRETTY_PRINT));

        return $report;
    }

    /**
     * Set up monitoring alerts
     */
    public function setupAlerts(): void
    {
        $alerts = [
            'high_error_rate' => [
                'condition' => 'error_rate > 5%',
                'action' => 'send_email_alert',
                'recipients' => ['admin@salemitra.com'],
            ],
            'slow_response_time' => [
                'condition' => 'avg_response_time > 2s',
                'action' => 'send_slack_alert',
                'webhook' => config('monitoring.slack_webhook'),
            ],
            'high_memory_usage' => [
                'condition' => 'memory_usage > 80%',
                'action' => 'send_sms_alert',
                'recipients' => ['+1234567890'],
            ],
            'database_connection_issues' => [
                'condition' => 'db_connection_failures > 5',
                'action' => 'send_email_alert',
                'recipients' => ['dba@salemitra.com'],
            ],
        ];

        Cache::put('monitoring_alerts', $alerts, 86400); // 24 hours
    }

    /**
     * Check alert conditions
     */
    public function checkAlerts(): array
    {
        $alerts = Cache::get('monitoring_alerts', []);
        $triggered = [];

        foreach ($alerts as $alertName => $alert) {
            if ($this->evaluateAlertCondition($alert['condition'])) {
                $triggered[] = [
                    'name' => $alertName,
                    'condition' => $alert['condition'],
                    'action' => $alert['action'],
                    'triggered_at' => now()->toISOString(),
                ];
            }
        }

        return $triggered;
    }

    /**
     * Get overall system status
     */
    private function getOverallStatus(): string
    {
        $health = $this->getSystemHealth();
        
        if ($health['database']['status'] === 'error' || 
            $health['cache']['status'] === 'error' || 
            $health['storage']['status'] === 'error') {
            return 'critical';
        }
        
        if ($health['memory']['usage'] > 80 || $health['disk']['usage'] > 90) {
            return 'warning';
        }
        
        return 'healthy';
    }

    /**
     * Check database health
     */
    private function checkDatabaseHealth(): array
    {
        try {
            DB::connection()->getPdo();
            $status = 'healthy';
            $responseTime = $this->measureDatabaseResponseTime();
        } catch (\Exception $e) {
            $status = 'error';
            $responseTime = null;
        }

        return [
            'status' => $status,
            'response_time' => $responseTime,
            'connections' => $this->getDatabaseConnections(),
        ];
    }

    /**
     * Check cache health
     */
    private function checkCacheHealth(): array
    {
        try {
            Cache::put('health_check', 'ok', 60);
            $status = Cache::get('health_check') === 'ok' ? 'healthy' : 'error';
        } catch (\Exception $e) {
            $status = 'error';
        }

        return [
            'status' => $status,
            'driver' => config('cache.default'),
        ];
    }

    /**
     * Check storage health
     */
    private function checkStorageHealth(): array
    {
        try {
            Storage::disk('local')->put('health_check.txt', 'ok');
            $status = Storage::disk('local')->get('health_check.txt') === 'ok' ? 'healthy' : 'error';
            Storage::disk('local')->delete('health_check.txt');
        } catch (\Exception $e) {
            $status = 'error';
        }

        return [
            'status' => $status,
            'disk_usage' => $this->getDiskUsage(),
        ];
    }

    /**
     * Get memory usage
     */
    private function getMemoryUsage(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->convertToBytes($memoryLimit);
        
        return [
            'current' => $memoryUsage,
            'peak' => memory_get_peak_usage(true),
            'limit' => $memoryLimitBytes,
            'usage' => round(($memoryUsage / $memoryLimitBytes) * 100, 2),
        ];
    }

    /**
     * Get disk usage
     */
    private function getDiskUsage(): array
    {
        $total = disk_total_space('/');
        $free = disk_free_space('/');
        $used = $total - $free;
        
        return [
            'total' => $total,
            'used' => $used,
            'free' => $free,
            'usage' => round(($used / $total) * 100, 2),
        ];
    }

    /**
     * Get error statistics
     */
    private function getErrorStats(): array
    {
        return [
            'last_24h' => Cache::get('error_count_24h', 0),
            'last_hour' => Cache::get('error_count_hour', 0),
            'critical_errors' => Cache::get('critical_error_count', 0),
        ];
    }

    /**
     * Get performance statistics
     */
    private function getPerformanceStats(): array
    {
        return [
            'avg_response_time' => Cache::get('avg_response_time', 0),
            'slow_queries' => Cache::get('slow_query_count', 0),
            'cache_hit_rate' => Cache::get('cache_hit_rate', 0),
        ];
    }

    /**
     * Increment error count
     */
    private function incrementErrorCount(string $errorMessage): void
    {
        Cache::increment('error_count_24h');
        Cache::increment('error_count_hour');
        
        if (str_contains($errorMessage, 'critical') || str_contains($errorMessage, 'fatal')) {
            Cache::increment('critical_error_count');
        }
    }

    /**
     * Measure database response time
     */
    private function measureDatabaseResponseTime(): float
    {
        $start = microtime(true);
        DB::select('SELECT 1');
        return round((microtime(true) - $start) * 1000, 2); // milliseconds
    }

    /**
     * Get database connections
     */
    private function getDatabaseConnections(): int
    {
        try {
            $result = DB::select("SHOW STATUS LIKE 'Threads_connected'");
            return $result[0]->Value ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get CPU usage
     */
    private function getCpuUsage(): float
    {
        // This would require system-specific implementation
        return 0.0;
    }

    /**
     * Convert memory limit to bytes
     */
    private function convertToBytes(string $memoryLimit): int
    {
        $unit = strtolower(substr($memoryLimit, -1));
        $value = (int) $memoryLimit;
        
        switch ($unit) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }

    /**
     * Evaluate alert condition
     */
    private function evaluateAlertCondition(string $condition): bool
    {
        // This would evaluate the condition string
        // For now, return false
        return false;
    }

    /**
     * Get user metrics
     */
    private function getUserMetrics(): array
    {
        return [
            'total' => Cache::get('user_count', 0),
            'active_today' => Cache::get('active_users_today', 0),
            'new_this_week' => Cache::get('new_users_week', 0),
        ];
    }

    /**
     * Get property metrics
     */
    private function getPropertyMetrics(): array
    {
        return [
            'total' => Cache::get('property_count', 0),
            'published' => Cache::get('published_properties', 0),
            'views_today' => Cache::get('property_views_today', 0),
        ];
    }

    /**
     * Get payment metrics
     */
    private function getPaymentMetrics(): array
    {
        return [
            'total_today' => Cache::get('payments_today', 0),
            'amount_today' => Cache::get('payment_amount_today', 0),
            'success_rate' => Cache::get('payment_success_rate', 0),
        ];
    }

    /**
     * Get API usage metrics
     */
    private function getApiUsageMetrics(): array
    {
        return [
            'requests_today' => Cache::get('api_requests_today', 0),
            'avg_response_time' => Cache::get('api_avg_response_time', 0),
            'error_rate' => Cache::get('api_error_rate', 0),
        ];
    }

    /**
     * Get error metrics
     */
    private function getErrorMetrics(): array
    {
        return [
            'total_today' => Cache::get('errors_today', 0),
            'critical_today' => Cache::get('critical_errors_today', 0),
            'resolved_today' => Cache::get('resolved_errors_today', 0),
        ];
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics(): array
    {
        return [
            'avg_response_time' => Cache::get('avg_response_time', 0),
            'slow_queries' => Cache::get('slow_queries', 0),
            'cache_hit_rate' => Cache::get('cache_hit_rate', 0),
        ];
    }

    /**
     * Get report summary
     */
    private function getReportSummary(string $period): array
    {
        return [
            'total_requests' => 0,
            'avg_response_time' => 0,
            'error_rate' => 0,
            'uptime' => 99.9,
        ];
    }

    /**
     * Get performance report
     */
    private function getPerformanceReport(string $period): array
    {
        return [
            'response_times' => [],
            'memory_usage' => [],
            'cpu_usage' => [],
        ];
    }

    /**
     * Get error report
     */
    private function getErrorReport(string $period): array
    {
        return [
            'error_types' => [],
            'error_trends' => [],
            'critical_errors' => [],
        ];
    }

    /**
     * Get usage report
     */
    private function getUsageReport(string $period): array
    {
        return [
            'api_usage' => [],
            'user_activity' => [],
            'feature_usage' => [],
        ];
    }

    /**
     * Get recommendations
     */
    private function getRecommendations(): array
    {
        return [
            'Consider implementing database query caching for better performance',
            'Monitor memory usage and consider increasing PHP memory limit if needed',
            'Set up automated backups for critical data',
            'Implement rate limiting for API endpoints',
        ];
    }
}
