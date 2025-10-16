<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueryOptimizationService
{
    /**
     * Optimize property queries with eager loading
     */
    public function optimizePropertyQuery(Builder $query): Builder
    {
        return $query->with([
            'organization:id,name,email',
            'tenants:id,property_id,name,status',
            'amenities:id,property_id,amenity_name',
            'availability:id,property_id,date,status'
        ])->select([
            'id', 'org_id', 'title', 'short_description', 'property_type',
            'category', 'price', 'city', 'locality', 'pincode', 'address_line',
            'furnished_status', 'bedrooms', 'bathrooms', 'area_sqft',
            'availability_status', 'published', 'featured', 'published_at',
            'created_at', 'updated_at'
        ]);
    }

    /**
     * Optimize tenant queries with eager loading
     */
    public function optimizeTenantQuery(Builder $query): Builder
    {
        return $query->with([
            'property:id,title,address_line,city',
            'organization:id,name'
        ])->select([
            'id', 'org_id', 'property_id', 'name', 'phone', 'email',
            'id_proof_type', 'id_proof_number', 'lease_start', 'lease_end',
            'rent_amount', 'security_deposit', 'status', 'created_at', 'updated_at'
        ]);
    }

    /**
     * Optimize invoice queries with eager loading
     */
    public function optimizeInvoiceQuery(Builder $query): Builder
    {
        return $query->with([
            'tenant:id,name,property_id',
            'property:id,title,address_line',
            'payments:id,invoice_id,amount,status,created_at'
        ])->select([
            'id', 'org_id', 'tenant_id', 'property_id', 'invoice_number',
            'amount', 'due_date', 'status', 'description', 'created_at', 'updated_at'
        ]);
    }

    /**
     * Optimize lead queries with eager loading
     */
    public function optimizeLeadQuery(Builder $query): Builder
    {
        return $query->with([
            'property:id,title,address_line,city,price',
            'property.organization:id,name'
        ])->select([
            'id', 'property_id', 'name', 'email', 'phone', 'message',
            'status', 'source', 'created_at', 'updated_at'
        ]);
    }

    /**
     * Get optimized analytics query
     */
    public function getOptimizedAnalyticsQuery(int $orgId, string $period = '30d'): array
    {
        $dateRange = $this->getDateRange($period);
        
        return [
            'properties' => DB::table('properties')
                ->where('org_id', $orgId)
                ->whereBetween('created_at', $dateRange)
                ->selectRaw('COUNT(*) as total, COUNT(CASE WHEN published = 1 THEN 1 END) as published')
                ->first(),
                
            'tenants' => DB::table('tenants')
                ->where('org_id', $orgId)
                ->whereBetween('created_at', $dateRange)
                ->selectRaw('COUNT(*) as total, COUNT(CASE WHEN status = "active" THEN 1 END) as active')
                ->first(),
                
            'invoices' => DB::table('invoices')
                ->where('org_id', $orgId)
                ->whereBetween('created_at', $dateRange)
                ->selectRaw('COUNT(*) as total, SUM(amount) as total_amount')
                ->first(),
                
            'payments' => DB::table('payments')
                ->where('org_id', $orgId)
                ->where('status', 'completed')
                ->whereBetween('created_at', $dateRange)
                ->selectRaw('COUNT(*) as total, SUM(amount) as total_amount')
                ->first(),
        ];
    }

    /**
     * Optimize search queries with full-text search
     */
    public function optimizeSearchQuery(string $searchTerm, array $filters = []): Builder
    {
        $query = DB::table('properties')
            ->where('published', true)
            ->select([
                'id', 'title', 'short_description', 'property_type', 'category',
                'price', 'city', 'locality', 'bedrooms', 'bathrooms', 'area_sqft',
                'furnished_status', 'published_at'
            ]);

        // Full-text search
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->whereFullText(['title', 'short_description'], $searchTerm)
                  ->orWhere('city', 'like', "%{$searchTerm}%")
                  ->orWhere('locality', 'like', "%{$searchTerm}%");
            });
        }

        // Apply filters
        foreach ($filters as $field => $value) {
            if (!empty($value)) {
                $query->where($field, $value);
            }
        }

        return $query;
    }

    /**
     * Get paginated results with optimization
     */
    public function getPaginatedResults(Builder $query, int $perPage = 15, int $page = 1): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count efficiently
        $total = $query->count();
        
        // Get paginated results
        $results = $query->offset($offset)->limit($perPage)->get();
        
        return [
            'data' => $results,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total),
            ]
        ];
    }

    /**
     * Optimize bulk operations
     */
    public function optimizeBulkOperation(string $table, array $ids, array $updates): bool
    {
        try {
            DB::beginTransaction();
            
            // Use chunking for large datasets
            $chunks = array_chunk($ids, 1000);
            
            foreach ($chunks as $chunk) {
                DB::table($table)
                    ->whereIn('id', $chunk)
                    ->update($updates);
            }
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Bulk operation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get database performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        try {
            $metrics = [];
            
            // Get slow query log (if enabled)
            $slowQueries = DB::select("SHOW STATUS LIKE 'Slow_queries'");
            $metrics['slow_queries'] = $slowQueries[0]->Value ?? 0;
            
            // Get connection count
            $connections = DB::select("SHOW STATUS LIKE 'Threads_connected'");
            $metrics['active_connections'] = $connections[0]->Value ?? 0;
            
            // Get query cache hit rate
            $cacheHits = DB::select("SHOW STATUS LIKE 'Qcache_hits'");
            $cacheInserts = DB::select("SHOW STATUS LIKE 'Qcache_inserts'");
            $metrics['query_cache_hit_rate'] = $this->calculateHitRate(
                $cacheHits[0]->Value ?? 0,
                $cacheInserts[0]->Value ?? 0
            );
            
            return $metrics;
            
        } catch (\Exception $e) {
            Log::error("Failed to get performance metrics: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Optimize database indexes
     */
    public function optimizeIndexes(): array
    {
        try {
            $optimizations = [];
            
            // Analyze tables
            $tables = ['properties', 'tenants', 'invoices', 'payments', 'leads'];
            
            foreach ($tables as $table) {
                DB::statement("ANALYZE TABLE {$table}");
                $optimizations[] = "Analyzed table: {$table}";
            }
            
            // Optimize tables
            foreach ($tables as $table) {
                DB::statement("OPTIMIZE TABLE {$table}");
                $optimizations[] = "Optimized table: {$table}";
            }
            
            return $optimizations;
            
        } catch (\Exception $e) {
            Log::error("Failed to optimize indexes: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get date range for analytics
     */
    private function getDateRange(string $period): array
    {
        $endDate = now();
        
        switch ($period) {
            case '7d':
                $startDate = $endDate->copy()->subDays(7);
                break;
            case '30d':
                $startDate = $endDate->copy()->subDays(30);
                break;
            case '90d':
                $startDate = $endDate->copy()->subDays(90);
                break;
            case '1y':
                $startDate = $endDate->copy()->subYear();
                break;
            default:
                $startDate = $endDate->copy()->subDays(30);
        }

        return [$startDate, $endDate];
    }

    /**
     * Calculate hit rate percentage
     */
    private function calculateHitRate(int $hits, int $inserts): float
    {
        $total = $hits + $inserts;
        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }
}
