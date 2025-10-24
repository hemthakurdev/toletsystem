<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        
        // Check if user is a frontend user (no organization)
        if ($user->user_type === 'frontend' || !$user->organization) {
            return $this->frontendUserDashboard($user);
        }
        
        // Organization user dashboard
        return $this->organizationUserDashboard($user);
    }
    
    private function frontendUserDashboard($user): Response
    {
        // Get frontend user dashboard statistics
        $stats = [
            'total_favorites' => 0, // Will be implemented when favorites are added
            'total_inquiries' => 0, // Will be implemented when inquiries are added
            'saved_searches' => 0, // Will be implemented when saved searches are added
            'recent_views' => 0, // Will be implemented when view tracking is added
        ];

        // Get recent activity for frontend user
        $recentActivity = collect([
            [
                'id' => 1,
                'message' => 'Welcome to your dashboard!',
                'type' => 'info',
                'created_at' => now(),
            ]
        ]);

        // Get upcoming tasks for frontend user
        $upcomingTasks = collect([
            [
                'id' => 1,
                'description' => 'Complete your profile',
                'due_date' => now()->addDays(7),
            ]
        ]);

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'upcomingTasks' => $upcomingTasks,
            'userType' => 'frontend',
        ]);
    }
    
    private function organizationUserDashboard($user): Response
    {
        $organization = $user->organization;

        // Get dashboard statistics
        $stats = [
            'total_properties' => $organization->properties()->count(),
            'occupied_units' => $organization->properties()->whereHas('tenants')->count(),
            'monthly_revenue' => $organization->invoices()->whereMonth('created_at', now()->month)->sum('amount'),
            'pending_invoices' => $organization->invoices()->where('status', 'pending')->count(),
            'total_tenants' => $organization->tenants()->count(),
            'total_leads' => $organization->leads()->count(),
            'total_revenue' => $organization->invoices()->where('status', 'paid')->sum('amount'),
            'collection_rate' => $this->calculateCollectionRate($organization),
            'available_units' => $organization->properties()->whereDoesntHave('tenants')->count(),
            'occupancy_rate' => $this->calculateOccupancyRate($organization),
            'average_rent' => $organization->properties()->avg('price') ?? 0,
            'total_area' => $organization->properties()->sum('area_sqft'),
            'paid_invoices' => $organization->invoices()->where('status', 'paid')->count(),
            'overdue_amount' => $organization->invoices()->where('status', 'overdue')->sum('amount'),
            'this_month_revenue' => $organization->invoices()->whereMonth('created_at', now()->month)->where('status', 'paid')->sum('amount'),
            'last_month_revenue' => $organization->invoices()->whereMonth('created_at', now()->subMonth()->month)->where('status', 'paid')->sum('amount'),
        ];

        // Get recent activity (notifications)
        $recentActivity = $organization->notifications()
            ->latest()
            ->limit(4)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'created_at' => $notification->created_at,
                ];
            });

        // Get upcoming tasks (invoices due soon)
        $upcomingTasks = $organization->invoices()
            ->where('status', 'pending')
            ->where('due_date', '<=', now()->addDays(7))
            ->latest('due_date')
            ->limit(3)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'description' => "Invoice #{$invoice->invoice_number} payment due",
                    'due_date' => $invoice->due_date,
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'upcomingTasks' => $upcomingTasks,
            'userType' => 'organization',
        ]);
    }

    private function calculateCollectionRate($organization): string
    {
        $totalInvoices = $organization->invoices()->count();
        if ($totalInvoices === 0) {
            return '0%';
        }

        $paidInvoices = $organization->invoices()->where('status', 'paid')->count();
        $rate = ($paidInvoices / $totalInvoices) * 100;
        
        return round($rate) . '%';
    }

    private function calculateOccupancyRate($organization): string
    {
        $totalProperties = $organization->properties()->count();
        if ($totalProperties === 0) {
            return '0%';
        }

        $occupiedProperties = $organization->properties()->whereHas('tenants')->count();
        $rate = ($occupiedProperties / $totalProperties) * 100;
        
        return round($rate) . '%';
    }
}
