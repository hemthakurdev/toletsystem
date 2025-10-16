<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get platform overview statistics
     */
    public function overview(): JsonResponse
    {
        $stats = [
            'total_organizations' => Organization::count(),
            'active_organizations' => Organization::where('status', 'active')->count(),
            'total_users' => User::whereNotNull('org_id')->count(),
            'total_properties' => Property::count(),
            'published_properties' => Property::where('published', true)->count(),
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'total_invoices' => Invoice::count(),
            'paid_invoices' => Invoice::where('status', 'paid')->count(),
            'total_payments' => Payment::where('payment_status', 'completed')->count(),
            'total_revenue' => Payment::where('payment_status', 'completed')->sum('amount'),
            'monthly_revenue' => Payment::where('payment_status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get revenue analytics
     */
    public function revenue(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month'); // day, week, month, year
        $limit = $request->get('limit', 12);

        $query = Payment::where('payment_status', 'completed')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as total_payments')
            )
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit($limit);

        switch ($period) {
            case 'day':
                $query->where('created_at', '>=', now()->subDays($limit));
                break;
            case 'week':
                $query->where('created_at', '>=', now()->subWeeks($limit));
                break;
            case 'month':
                $query->where('created_at', '>=', now()->subMonths($limit));
                break;
            case 'year':
                $query->where('created_at', '>=', now()->subYears($limit));
                break;
        }

        $revenueData = $query->get();

        // Get payment method breakdown
        $paymentMethods = Payment::where('payment_status', 'completed')
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get();

        // Get organization revenue breakdown
        $organizationRevenue = Organization::withSum('payments', 'amount')
            ->withCount('payments')
            ->orderBy('payments_sum_amount', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'revenue_trend' => $revenueData,
                'payment_methods' => $paymentMethods,
                'top_organizations' => $organizationRevenue,
            ],
        ]);
    }

    /**
     * Get user analytics
     */
    public function users(): JsonResponse
    {
        $userStats = [
            'total_users' => User::whereNotNull('org_id')->count(),
            'active_users' => User::whereNotNull('org_id')->where('last_login', '>=', now()->subDays(30))->count(),
            'new_users_this_month' => User::whereNotNull('org_id')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'users_by_role' => User::whereNotNull('org_id')
                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->select('roles.name', DB::raw('COUNT(*) as count'))
                ->groupBy('roles.name')
                ->get(),
        ];

        // Get user registration trend
        $registrationTrend = User::whereNotNull('org_id')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $userStats,
                'registration_trend' => $registrationTrend,
            ],
        ]);
    }

    /**
     * Get property analytics
     */
    public function properties(): JsonResponse
    {
        $propertyStats = [
            'total_properties' => Property::count(),
            'published_properties' => Property::where('published', true)->count(),
            'featured_properties' => Property::where('featured', true)->count(),
            'properties_by_type' => Property::select('property_type', DB::raw('COUNT(*) as count'))
                ->groupBy('property_type')
                ->get(),
            'properties_by_status' => Property::select('availability_status', DB::raw('COUNT(*) as count'))
                ->groupBy('availability_status')
                ->get(),
            'properties_by_city' => Property::select('city', DB::raw('COUNT(*) as count'))
                ->groupBy('city')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
        ];

        // Get property creation trend
        $creationTrend = Property::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $propertyStats,
                'creation_trend' => $creationTrend,
            ],
        ]);
    }

    /**
     * Get tenant analytics
     */
    public function tenants(): JsonResponse
    {
        $tenantStats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'tenants_by_status' => Tenant::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get(),
            'average_rent' => Tenant::avg('rent_amount'),
            'total_rent_collected' => Payment::where('payment_status', 'completed')
                ->whereHas('invoice', function ($query) {
                    $query->where('type', 'rent');
                })
                ->sum('amount'),
        ];

        // Get tenant registration trend
        $registrationTrend = Tenant::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $tenantStats,
                'registration_trend' => $registrationTrend,
            ],
        ]);
    }

    /**
     * Get invoice analytics
     */
    public function invoices(): JsonResponse
    {
        $invoiceStats = [
            'total_invoices' => Invoice::count(),
            'paid_invoices' => Invoice::where('status', 'paid')->count(),
            'pending_invoices' => Invoice::where('status', 'pending')->count(),
            'overdue_invoices' => Invoice::where('status', 'overdue')->count(),
            'invoices_by_type' => Invoice::select('type', DB::raw('COUNT(*) as count'))
                ->groupBy('type')
                ->get(),
            'invoices_by_status' => Invoice::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get(),
            'total_invoice_amount' => Invoice::sum('total_amount'),
            'paid_invoice_amount' => Invoice::where('status', 'paid')->sum('total_amount'),
            'pending_invoice_amount' => Invoice::where('status', 'pending')->sum('total_amount'),
        ];

        // Get invoice creation trend
        $creationTrend = Invoice::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $invoiceStats,
                'creation_trend' => $creationTrend,
            ],
        ]);
    }

    /**
     * Get organization analytics
     */
    public function organizations(): JsonResponse
    {
        $orgStats = [
            'total_organizations' => Organization::count(),
            'active_organizations' => Organization::where('status', 'active')->count(),
            'suspended_organizations' => Organization::where('status', 'suspended')->count(),
            'organizations_by_plan' => Organization::with('plan')
                ->select('plan_id', DB::raw('COUNT(*) as count'))
                ->groupBy('plan_id')
                ->get(),
            'organizations_by_status' => Organization::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get(),
            'organizations_by_city' => Organization::select('city', DB::raw('COUNT(*) as count'))
                ->groupBy('city')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
        ];

        // Get organization registration trend
        $registrationTrend = Organization::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $orgStats,
                'registration_trend' => $registrationTrend,
            ],
        ]);
    }

    /**
     * Get system health metrics
     */
    public function systemHealth(): JsonResponse
    {
        $health = [
            'database_connections' => DB::connection()->getPdo() ? 'healthy' : 'unhealthy',
            'total_storage_used' => $this->getStorageUsage(),
            'active_sessions' => 0, // Would need session tracking
            'error_rate' => 0, // Would need error tracking
            'response_time' => 0, // Would need performance monitoring
            'uptime' => $this->getUptime(),
        ];

        return response()->json([
            'success' => true,
            'data' => $health,
        ]);
    }

    /**
     * Get storage usage
     */
    private function getStorageUsage(): array
    {
        $storagePath = storage_path();
        $totalSize = 0;
        $fileCount = 0;

        if (is_dir($storagePath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($storagePath)
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $totalSize += $file->getSize();
                    $fileCount++;
                }
            }
        }

        return [
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'file_count' => $fileCount,
        ];
    }

    /**
     * Get system uptime
     */
    private function getUptime(): string
    {
        if (function_exists('sys_getloadavg')) {
            $uptime = shell_exec('uptime');
            return trim($uptime);
        }

        return 'N/A';
    }
}
