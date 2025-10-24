<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
    });

    // Public marketplace routes
    Route::prefix('properties')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\PropertyController::class, 'index']);
        Route::get('{id}', [\App\Http\Controllers\Api\PropertyController::class, 'show']);
        Route::post('{id}/contact', [\App\Http\Controllers\Api\PropertyController::class, 'contact']);
    });

    // Public lead creation (from marketplace)
    Route::post('leads', [\App\Http\Controllers\Api\LeadController::class, 'store']);

    // Public search routes
    Route::prefix('search')->group(function () {
        Route::get('properties', [\App\Http\Controllers\Api\SearchController::class, 'properties']);
        Route::get('cities', [\App\Http\Controllers\Api\SearchController::class, 'cities']);
        Route::get('localities', [\App\Http\Controllers\Api\SearchController::class, 'localities']);
        Route::get('property-types', [\App\Http\Controllers\Api\SearchController::class, 'propertyTypes']);
        Route::get('featured-properties', [\App\Http\Controllers\Api\SearchController::class, 'featuredProperties']);
        Route::get('recent-properties', [\App\Http\Controllers\Api\SearchController::class, 'recentProperties']);
        Route::get('suggestions', [\App\Http\Controllers\Api\SearchController::class, 'suggestions']);
        Route::get('statistics', [\App\Http\Controllers\Api\SearchController::class, 'statistics']);
    });
});

// Protected routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });

    // Organization routes
    Route::prefix('org')->middleware('tenant.scope')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\OrganizationController::class, 'show']);
        Route::put('/', [\App\Http\Controllers\Api\OrganizationController::class, 'update']);
        
        // Properties
        Route::apiResource('properties', \App\Http\Controllers\Api\PropertyController::class)->names([
            'index' => 'api.properties.index',
            'store' => 'api.properties.store',
            'show' => 'api.properties.show',
            'update' => 'api.properties.update',
            'destroy' => 'api.properties.destroy',
        ]);
        Route::post('properties/{id}/publish', [\App\Http\Controllers\Api\PropertyController::class, 'publish']);
        Route::post('properties/{id}/unpublish', [\App\Http\Controllers\Api\PropertyController::class, 'unpublish']);
        
        // File upload routes
        Route::post('properties/{property}/images', [\App\Http\Controllers\Api\FileUploadController::class, 'uploadPropertyImages']);
        Route::delete('properties/{property}/images', [\App\Http\Controllers\Api\FileUploadController::class, 'deletePropertyImages']);
        Route::get('properties/{property}/images', [\App\Http\Controllers\Api\FileUploadController::class, 'getPropertyImages']);
        Route::post('documents/upload', [\App\Http\Controllers\Api\FileUploadController::class, 'uploadDocument']);
        Route::delete('documents', [\App\Http\Controllers\Api\FileUploadController::class, 'deleteDocument']);
        Route::get('images/url', [\App\Http\Controllers\Api\FileUploadController::class, 'getImageUrl']);
        
        // Tenants
        Route::apiResource('tenants', \App\Http\Controllers\Api\TenantController::class)->names([
            'index' => 'api.tenants.index',
            'store' => 'api.tenants.store',
            'show' => 'api.tenants.show',
            'update' => 'api.tenants.update',
            'destroy' => 'api.tenants.destroy',
        ]);
        
        // Invoices
        Route::apiResource('invoices', \App\Http\Controllers\Api\InvoiceController::class)->names([
            'index' => 'api.invoices.index',
            'store' => 'api.invoices.store',
            'show' => 'api.invoices.show',
            'update' => 'api.invoices.update',
            'destroy' => 'api.invoices.destroy',
        ]);
        Route::get('invoices/{id}/pdf', [\App\Http\Controllers\Api\InvoiceController::class, 'downloadPdf']);
        Route::post('invoices/{id}/send', [\App\Http\Controllers\Api\InvoiceController::class, 'send']);
        Route::get('invoices/statistics', [\App\Http\Controllers\Api\InvoiceController::class, 'statistics']);
        Route::post('invoices/generate-monthly-rent', [\App\Http\Controllers\Api\InvoiceController::class, 'generateMonthlyRentInvoices']);
        
        // Payments
        Route::apiResource('payments', \App\Http\Controllers\Api\PaymentController::class);
        Route::post('payments/create', [\App\Http\Controllers\Api\PaymentController::class, 'createPayment']);
        Route::post('payments/complete', [\App\Http\Controllers\Api\PaymentController::class, 'completePayment']);
        Route::post('payments/{payment}/refund', [\App\Http\Controllers\Api\PaymentController::class, 'refund']);
        Route::get('payments/statistics', [\App\Http\Controllers\Api\PaymentController::class, 'statistics']);
        Route::get('payments/config', [\App\Http\Controllers\Api\PaymentController::class, 'getConfig']);
        
        // Leads
        Route::apiResource('leads', \App\Http\Controllers\Api\LeadController::class)->names([
            'index' => 'api.leads.index',
            'store' => 'api.leads.store',
            'show' => 'api.leads.show',
            'update' => 'api.leads.update',
            'destroy' => 'api.leads.destroy',
        ]);
        Route::post('leads/{lead}/mark-contacted', [\App\Http\Controllers\Api\LeadController::class, 'markContacted']);
        Route::post('leads/{lead}/mark-converted', [\App\Http\Controllers\Api\LeadController::class, 'markConverted']);
        Route::post('leads/{lead}/mark-not-interested', [\App\Http\Controllers\Api\LeadController::class, 'markNotInterested']);
        Route::post('leads/{lead}/conversations', [\App\Http\Controllers\Api\LeadController::class, 'addConversation']);
        Route::get('leads/statistics', [\App\Http\Controllers\Api\LeadController::class, 'statistics']);
        
        // Expenses
        Route::apiResource('expenses', \App\Http\Controllers\Api\ExpenseController::class)->names([
            'index' => 'api.expenses.index',
            'store' => 'api.expenses.store',
            'show' => 'api.expenses.show',
            'update' => 'api.expenses.update',
            'destroy' => 'api.expenses.destroy',
        ]);
        Route::post('expenses/{id}/approve', [\App\Http\Controllers\Api\ExpenseController::class, 'approve']);
        Route::post('expenses/{id}/reject', [\App\Http\Controllers\Api\ExpenseController::class, 'reject']);
        
        // Documents
        Route::apiResource('documents', \App\Http\Controllers\Api\DocumentController::class)->names([
            'index' => 'api.documents.index',
            'store' => 'api.documents.store',
            'show' => 'api.documents.show',
            'update' => 'api.documents.update',
            'destroy' => 'api.documents.destroy',
        ]);
        Route::get('documents/{id}/download', [\App\Http\Controllers\Api\DocumentController::class, 'download']);
        
        // Analytics
        Route::prefix('analytics')->group(function () {
            Route::get('dashboard', [\App\Http\Controllers\Api\AnalyticsController::class, 'dashboard']);
            Route::get('properties/{id}/performance', [\App\Http\Controllers\Api\AnalyticsController::class, 'propertyPerformance']);
            Route::get('financial', [\App\Http\Controllers\Api\AnalyticsController::class, 'financial']);
            
            // Advanced Analytics
            Route::get('overview', [\App\Http\Controllers\Api\AnalyticsController::class, 'overview']);
            Route::get('revenue', [\App\Http\Controllers\Api\AnalyticsController::class, 'revenue']);
            Route::get('users', [\App\Http\Controllers\Api\AnalyticsController::class, 'users']);
            Route::get('properties', [\App\Http\Controllers\Api\AnalyticsController::class, 'properties']);
            Route::get('property-types', [\App\Http\Controllers\Api\AnalyticsController::class, 'propertyTypes']);
            Route::get('leads', [\App\Http\Controllers\Api\AnalyticsController::class, 'leads']);
        });
        
        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('financial', [\App\Http\Controllers\Api\ReportController::class, 'financial']);
            Route::get('properties', [\App\Http\Controllers\Api\ReportController::class, 'properties']);
            Route::get('tenants', [\App\Http\Controllers\Api\ReportController::class, 'tenants']);
        });
        
        // Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
            Route::post('mark-read', [\App\Http\Controllers\Api\NotificationController::class, 'markRead']);
            Route::post('mark-all-read', [\App\Http\Controllers\Api\NotificationController::class, 'markAllRead']);
        });
        
        // Subscription
        Route::prefix('subscription')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\SubscriptionController::class, 'show']);
            Route::post('create', [\App\Http\Controllers\Api\SubscriptionController::class, 'create']);
            Route::post('cancel', [\App\Http\Controllers\Api\SubscriptionController::class, 'cancel']);
        });
        
        // Bulk Operations
        Route::prefix('bulk')->group(function () {
            Route::post('properties/delete', [\App\Http\Controllers\Api\BulkOperationsController::class, 'bulkDeleteProperties']);
            Route::post('properties/update-status', [\App\Http\Controllers\Api\BulkOperationsController::class, 'bulkUpdatePropertyStatus']);
            Route::post('tenants/delete', [\App\Http\Controllers\Api\BulkOperationsController::class, 'bulkDeleteTenants']);
            Route::post('invoices/generate', [\App\Http\Controllers\Api\BulkOperationsController::class, 'bulkGenerateInvoices']);
            Route::post('expenses/approve', [\App\Http\Controllers\Api\BulkOperationsController::class, 'bulkApproveExpenses']);
            Route::post('expenses/reject', [\App\Http\Controllers\Api\BulkOperationsController::class, 'bulkRejectExpenses']);
            Route::post('documents/delete', [\App\Http\Controllers\Api\BulkOperationsController::class, 'bulkDeleteDocuments']);
            Route::post('leads/update-status', [\App\Http\Controllers\Api\BulkOperationsController::class, 'bulkUpdateLeadStatus']);
            Route::post('notifications/send', [\App\Http\Controllers\Api\BulkOperationsController::class, 'bulkSendNotifications']);
            Route::post('properties/import', [\App\Http\Controllers\Api\BulkOperationsController::class, 'bulkImportProperties']);
            Route::get('statistics', [\App\Http\Controllers\Api\BulkOperationsController::class, 'getBulkOperationStats']);
        });
        
        // Advanced Search
        Route::prefix('search')->group(function () {
            Route::post('properties/advanced', [\App\Http\Controllers\Api\AdvancedSearchController::class, 'advancedPropertySearch']);
            Route::post('tenants/advanced', [\App\Http\Controllers\Api\AdvancedSearchController::class, 'advancedTenantSearch']);
            Route::post('invoices/advanced', [\App\Http\Controllers\Api\AdvancedSearchController::class, 'advancedInvoiceSearch']);
            Route::post('global', [\App\Http\Controllers\Api\AdvancedSearchController::class, 'globalSearch']);
            Route::post('suggestions', [\App\Http\Controllers\Api\AdvancedSearchController::class, 'getSearchSuggestions']);
        });
        
        // Export
        Route::prefix('exports')->group(function () {
            Route::post('properties/excel', [\App\Http\Controllers\Api\ExportController::class, 'exportPropertiesToExcel']);
            Route::post('tenants/excel', [\App\Http\Controllers\Api\ExportController::class, 'exportTenantsToExcel']);
            Route::post('invoices/pdf', [\App\Http\Controllers\Api\ExportController::class, 'exportInvoicesToPdf']);
            Route::post('financial/excel', [\App\Http\Controllers\Api\ExportController::class, 'exportFinancialReportToExcel']);
            Route::get('history', [\App\Http\Controllers\Api\ExportController::class, 'getExportHistory']);
            Route::post('cleanup', [\App\Http\Controllers\Api\ExportController::class, 'cleanupOldExports']);
        });
    });

    // Admin routes
    Route::prefix('admin')->middleware(['auth:sanctum', 'role:super_admin'])->group(function () {
        Route::apiResource('organizations', \App\Http\Controllers\Api\Admin\OrganizationController::class);
        Route::post('organizations/{organization}/suspend', [\App\Http\Controllers\Api\Admin\OrganizationController::class, 'suspend']);
        Route::post('organizations/{organization}/activate', [\App\Http\Controllers\Api\Admin\OrganizationController::class, 'activate']);
        Route::get('organizations/statistics', [\App\Http\Controllers\Api\Admin\OrganizationController::class, 'statistics']);
        
        Route::apiResource('users', \App\Http\Controllers\Api\Admin\UserController::class);
        Route::post('users/{user}/suspend', [\App\Http\Controllers\Api\Admin\UserController::class, 'suspend']);
        Route::post('users/{user}/activate', [\App\Http\Controllers\Api\Admin\UserController::class, 'activate']);
        Route::get('users/statistics', [\App\Http\Controllers\Api\Admin\UserController::class, 'statistics']);
        Route::post('users/bulk-activate', [\App\Http\Controllers\Api\Admin\UserController::class, 'bulkActivate']);
        Route::post('users/bulk-suspend', [\App\Http\Controllers\Api\Admin\UserController::class, 'bulkSuspend']);
        
        Route::apiResource('plans', \App\Http\Controllers\Api\Admin\PlanController::class);
        
        Route::prefix('listings')->group(function () {
            Route::get('pending', [\App\Http\Controllers\Api\Admin\ListingController::class, 'pending']);
            Route::get('flagged', [\App\Http\Controllers\Api\Admin\ListingController::class, 'flagged']);
            Route::post('{property}/approve', [\App\Http\Controllers\Api\Admin\ListingController::class, 'approve']);
            Route::post('{property}/reject', [\App\Http\Controllers\Api\Admin\ListingController::class, 'reject']);
            Route::post('{property}/resolve-flag', [\App\Http\Controllers\Api\Admin\ListingController::class, 'resolveFlag']);
            Route::get('statistics', [\App\Http\Controllers\Api\Admin\ListingController::class, 'statistics']);
            Route::post('bulk-approve', [\App\Http\Controllers\Api\Admin\ListingController::class, 'bulkApprove']);
            Route::post('bulk-reject', [\App\Http\Controllers\Api\Admin\ListingController::class, 'bulkReject']);
        });
        
        Route::prefix('analytics')->group(function () {
            Route::get('overview', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'overview']);
            Route::get('revenue', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'revenue']);
            Route::get('users', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'users']);
            Route::get('properties', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'properties']);
            Route::get('tenants', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'tenants']);
            Route::get('invoices', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'invoices']);
            Route::get('organizations', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'organizations']);
            Route::get('system-health', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'systemHealth']);
        });
    });
});

// Webhook routes
Route::prefix('v1/webhooks')->group(function () {
    Route::post('razorpay', [\App\Http\Controllers\Api\PaymentController::class, 'webhook']);
    Route::post('custom/{webhookType}', [\App\Http\Controllers\Api\WebhookController::class, 'handleCustomWebhook']);
    Route::post('test', [\App\Http\Controllers\Api\WebhookController::class, 'testWebhook']);
    Route::get('stats', [\App\Http\Controllers\Api\WebhookController::class, 'getWebhookStats']);
});


// Export download route (public)
Route::prefix('v1/exports')->group(function () {
    Route::get('download/{filename}', [\App\Http\Controllers\Api\ExportController::class, 'downloadExport']);
});
