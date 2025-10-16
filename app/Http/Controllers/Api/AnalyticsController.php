<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Document;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Get comprehensive analytics overview
     */
    public function overview(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', '30d');
            $dateRange = $this->getDateRange($period);
            $orgId = auth()->user()->org_id;

            // Revenue data
            $totalRevenue = Payment::where('org_id', $orgId)
                ->where('status', 'completed')
                ->whereBetween('created_at', $dateRange)
                ->sum('amount');

            $previousRevenue = Payment::where('org_id', $orgId)
                ->where('status', 'completed')
                ->whereBetween('created_at', $this->getPreviousDateRange($period))
                ->sum('amount');

            $revenueGrowth = $previousRevenue > 0 ? (($totalRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;

            // User data
            $activeUsers = User::where('org_id', $orgId)
                ->where('last_login_at', '>=', now()->subDays(30))
                ->count();

            $previousActiveUsers = User::where('org_id', $orgId)
                ->where('last_login_at', '>=', now()->subDays(60))
                ->where('last_login_at', '<', now()->subDays(30))
                ->count();

            $userGrowth = $previousActiveUsers > 0 ? (($activeUsers - $previousActiveUsers) / $previousActiveUsers) * 100 : 0;

            // Property data
            $totalProperties = Property::where('org_id', $orgId)->count();
            $publishedProperties = Property::where('org_id', $orgId)->where('published', true)->count();

            // Lead data
            $totalLeads = Lead::whereHas('property', function ($query) use ($orgId) {
                $query->where('org_id', $orgId);
            })->count();

            $convertedLeads = Lead::whereHas('property', function ($query) use ($orgId) {
                $query->where('org_id', $orgId);
            })->where('status', 'converted')->count();

            $conversionRate = $totalLeads > 0 ? ($convertedLeads / $totalLeads) * 100 : 0;

            // Performance metrics (mock data for now)
            $avgResponseTime = rand(100, 500);
            $uptime = 99.9;
            $satisfactionScore = 4.5;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_revenue' => $totalRevenue,
                    'revenue_growth' => round($revenueGrowth, 2),
                    'active_users' => $activeUsers,
                    'user_growth' => round($userGrowth, 2),
                    'total_properties' => $totalProperties,
                    'published_properties' => $publishedProperties,
                    'conversion_rate' => round($conversionRate, 2),
                    'total_leads' => $totalLeads,
                    'avg_response_time' => $avgResponseTime,
                    'uptime' => $uptime,
                    'satisfaction_score' => $satisfactionScore,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load overview data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get revenue analytics with chart data
     */
    public function revenue(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', '30d');
            $dateRange = $this->getDateRange($period);
            $orgId = auth()->user()->org_id;

            // Get revenue data
            $revenueData = Payment::where('org_id', $orgId)
                ->where('status', 'completed')
                ->whereBetween('created_at', $dateRange)
                ->selectRaw('DATE(created_at) as date, SUM(amount) as revenue')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Get payment data
            $paymentData = Payment::where('org_id', $orgId)
                ->whereBetween('created_at', $dateRange)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as payments')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Generate chart data
            $chartData = $this->generateChartData($dateRange, [
                'revenue' => $revenueData->pluck('revenue', 'date'),
                'payments' => $paymentData->pluck('payments', 'date'),
            ]);

            $totalRevenue = $revenueData->sum('revenue');
            $averageDaily = $totalRevenue / max($chartData['labels']->count(), 1);

            // Calculate growth rate
            $previousRevenue = Payment::where('org_id', $orgId)
                ->where('status', 'completed')
                ->whereBetween('created_at', $this->getPreviousDateRange($period))
                ->sum('amount');

            $growthRate = $previousRevenue > 0 ? (($totalRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_revenue' => $totalRevenue,
                    'average_daily' => $averageDaily,
                    'growth_rate' => round($growthRate, 2),
                    'chart_data' => $chartData,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load revenue data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user analytics with chart data
     */
    public function users(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', '30d');
            $dateRange = $this->getDateRange($period);
            $orgId = auth()->user()->org_id;

            // Get user registration data
            $newUsersData = User::where('org_id', $orgId)
                ->whereBetween('created_at', $dateRange)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as new_users')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Get active users data (users who logged in)
            $activeUsersData = User::where('org_id', $orgId)
                ->whereBetween('last_login_at', $dateRange)
                ->selectRaw('DATE(last_login_at) as date, COUNT(DISTINCT id) as active_users')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Get total users data
            $totalUsersData = User::where('org_id', $orgId)
                ->where('created_at', '<=', $dateRange[1])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as total_users')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Generate chart data
            $chartData = $this->generateChartData($dateRange, [
                'new_users' => $newUsersData->pluck('new_users', 'date'),
                'active_users' => $activeUsersData->pluck('active_users', 'date'),
                'total_users' => $totalUsersData->pluck('total_users', 'date'),
            ]);

            $newUsers = $newUsersData->sum('new_users');
            $activeUsers = $activeUsersData->sum('active_users');
            $totalUsers = User::where('org_id', $orgId)->count();

            // Calculate growth rate
            $previousNewUsers = User::where('org_id', $orgId)
                ->whereBetween('created_at', $this->getPreviousDateRange($period))
                ->count();

            $growthRate = $previousNewUsers > 0 ? (($newUsers - $previousNewUsers) / $previousNewUsers) * 100 : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'new_users' => $newUsers,
                    'active_users' => $activeUsers,
                    'total_users' => $totalUsers,
                    'growth_rate' => round($growthRate, 2),
                    'chart_data' => $chartData,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load user data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get property analytics with chart data
     */
    public function properties(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', '30d');
            $dateRange = $this->getDateRange($period);
            $orgId = auth()->user()->org_id;

            // Get property data
            $propertiesAddedData = Property::where('org_id', $orgId)
                ->whereBetween('created_at', $dateRange)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as properties_added')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $propertiesPublishedData = Property::where('org_id', $orgId)
                ->whereBetween('published_at', $dateRange)
                ->selectRaw('DATE(published_at) as date, COUNT(*) as properties_published')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Mock views data (in real app, this would come from analytics tracking)
            $viewsData = collect();
            $currentDate = $dateRange[0]->copy();
            while ($currentDate <= $dateRange[1]) {
                $viewsData->push([
                    'date' => $currentDate->format('Y-m-d'),
                    'views' => rand(50, 200)
                ]);
                $currentDate->addDay();
            }

            // Generate chart data
            $chartData = $this->generateChartData($dateRange, [
                'properties_added' => $propertiesAddedData->pluck('properties_added', 'date'),
                'properties_published' => $propertiesPublishedData->pluck('properties_published', 'date'),
                'views' => $viewsData->pluck('views', 'date'),
            ]);

            $totalProperties = Property::where('org_id', $orgId)->count();
            $publishedProperties = Property::where('org_id', $orgId)->where('published', true)->count();
            $pendingProperties = Property::where('org_id', $orgId)->where('status', 'pending')->count();
            $totalViews = $viewsData->sum('views');

            return response()->json([
                'success' => true,
                'data' => [
                    'total_properties' => $totalProperties,
                    'published_properties' => $publishedProperties,
                    'pending_properties' => $pendingProperties,
                    'total_views' => $totalViews,
                    'chart_data' => $chartData,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load property data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get financial analytics with chart data
     */
    public function financial(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', '30d');
            $dateRange = $this->getDateRange($period);
            $orgId = auth()->user()->org_id;

            // Get revenue data
            $revenueData = Payment::where('org_id', $orgId)
                ->where('status', 'completed')
                ->whereBetween('created_at', $dateRange)
                ->selectRaw('DATE(created_at) as date, SUM(amount) as revenue')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Get expense data
            $expenseData = Expense::where('org_id', $orgId)
                ->where('status', 'approved')
                ->whereBetween('created_at', $dateRange)
                ->selectRaw('DATE(created_at) as date, SUM(amount) as expenses')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Generate chart data
            $chartData = $this->generateChartData($dateRange, [
                'revenue' => $revenueData->pluck('revenue', 'date'),
                'expenses' => $expenseData->pluck('expenses', 'date'),
            ]);

            // Calculate profit data
            $profitData = [];
            foreach ($chartData['labels'] as $index => $label) {
                $revenue = $chartData['revenue'][$index] ?? 0;
                $expenses = $chartData['expenses'][$index] ?? 0;
                $profitData[] = $revenue - $expenses;
            }
            $chartData['profit'] = $profitData;

            $totalRevenue = $revenueData->sum('revenue');
            $totalExpenses = $expenseData->sum('expenses');
            $netProfit = $totalRevenue - $totalExpenses;
            $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_revenue' => $totalRevenue,
                    'total_expenses' => $totalExpenses,
                    'net_profit' => $netProfit,
                    'profit_margin' => round($profitMargin, 2),
                    'chart_data' => $chartData,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load financial data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get property type distribution
     */
    public function propertyTypes(Request $request): JsonResponse
    {
        try {
            $category = $request->get('category', 'all');
            $orgId = auth()->user()->org_id;

            $query = Property::where('org_id', $orgId);

            if ($category !== 'all') {
                $query->where('category', $category);
            }

            $propertyTypes = $query->selectRaw('property_type, COUNT(*) as count')
                ->groupBy('property_type')
                ->orderBy('count', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'name' => ucfirst($item->property_type),
                        'count' => $item->count,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'types' => $propertyTypes,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load property type data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get lead conversion analytics
     */
    public function leads(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', '30d');
            $dateRange = $this->getDateRange($period);
            $orgId = auth()->user()->org_id;

            // Get lead data by status
            $leadStats = Lead::whereHas('property', function ($query) use ($orgId) {
                $query->where('org_id', $orgId);
            })
            ->whereBetween('created_at', $dateRange)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

            $newLeads = $leadStats->get('new', (object)['count' => 0])->count;
            $contactedLeads = $leadStats->get('contacted', (object)['count' => 0])->count;
            $interestedLeads = $leadStats->get('interested', (object)['count' => 0])->count;
            $convertedLeads = $leadStats->get('converted', (object)['count' => 0])->count;

            $totalLeads = $newLeads + $contactedLeads + $interestedLeads + $convertedLeads;
            $conversionRate = $totalLeads > 0 ? ($convertedLeads / $totalLeads) * 100 : 0;

            $funnelData = [
                'labels' => ['New', 'Contacted', 'Interested', 'Converted'],
                'values' => [$newLeads, $contactedLeads, $interestedLeads, $convertedLeads],
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'new_leads' => $newLeads,
                    'contacted_leads' => $contactedLeads,
                    'interested_leads' => $interestedLeads,
                    'converted_leads' => $convertedLeads,
                    'conversion_rate' => round($conversionRate, 2),
                    'funnel_data' => $funnelData,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load lead data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get date range based on period
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
     * Get previous date range for comparison
     */
    private function getPreviousDateRange(string $period): array
    {
        $endDate = now();
        
        switch ($period) {
            case '7d':
                $startDate = $endDate->copy()->subDays(14);
                $endDate = $endDate->copy()->subDays(7);
                break;
            case '30d':
                $startDate = $endDate->copy()->subDays(60);
                $endDate = $endDate->copy()->subDays(30);
                break;
            case '90d':
                $startDate = $endDate->copy()->subDays(180);
                $endDate = $endDate->copy()->subDays(90);
                break;
            case '1y':
                $startDate = $endDate->copy()->subYears(2);
                $endDate = $endDate->copy()->subYear();
                break;
            default:
                $startDate = $endDate->copy()->subDays(60);
                $endDate = $endDate->copy()->subDays(30);
        }

        return [$startDate, $endDate];
    }

    /**
     * Generate chart data with consistent date range
     */
    private function generateChartData(array $dateRange, array $dataSets): array
    {
        $labels = [];
        $currentDate = $dateRange[0]->copy();
        
        while ($currentDate <= $dateRange[1]) {
            $labels[] = $currentDate->format('M j');
            $currentDate->addDay();
        }

        $chartData = ['labels' => $labels];
        
        foreach ($dataSets as $key => $data) {
            $values = [];
            $currentDate = $dateRange[0]->copy();
            
            while ($currentDate <= $dateRange[1]) {
                $dateKey = $currentDate->format('Y-m-d');
                $values[] = $data->get($dateKey, 0);
                $currentDate->addDay();
            }
            
            $chartData[$key] = $values;
        }

        return $chartData;
    }
}