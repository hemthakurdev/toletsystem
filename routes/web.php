<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Marketplace/Index');
})->name('home');

Route::get('/choose-account-type', function () {
    return Inertia::render('Auth/AccountTypeSelection');
})->name('account-type-selection');

Route::get('/test', function () {
    return Inertia::render('Test');
});

Route::get('/api-test', function () {
    return response()->json(['message' => 'Laravel is working!', 'timestamp' => now()]);
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::get('/properties', [App\Http\Controllers\PropertiesController::class, 'index'])
    ->middleware(['auth'])
    ->name('properties.index');

Route::get('/properties/create', [App\Http\Controllers\PropertiesController::class, 'create'])
    ->middleware(['auth'])
    ->name('properties.create');

Route::post('/properties', [App\Http\Controllers\PropertiesController::class, 'store'])
    ->middleware(['auth'])
    ->name('properties.store');

Route::get('/properties/{property}', [App\Http\Controllers\PropertiesController::class, 'show'])
    ->middleware(['auth'])
    ->name('properties.show');

Route::get('/properties/{property}/edit', [App\Http\Controllers\PropertiesController::class, 'edit'])
    ->middleware(['auth'])
    ->name('properties.edit');

Route::put('/properties/{property}', [App\Http\Controllers\PropertiesController::class, 'update'])
    ->middleware(['auth'])
    ->name('properties.update');

Route::delete('/properties/{property}', [App\Http\Controllers\PropertiesController::class, 'destroy'])
    ->middleware(['auth'])
    ->name('properties.destroy');

Route::get('/tenants', [App\Http\Controllers\TenantsController::class, 'index'])
    ->middleware(['auth'])
    ->name('tenants.index');

Route::get('/tenants/create', [App\Http\Controllers\TenantsController::class, 'create'])
    ->middleware(['auth'])
    ->name('tenants.create');

Route::get('/tenants/{tenant}', [App\Http\Controllers\TenantsController::class, 'show'])
    ->middleware(['auth'])
    ->name('tenants.show');

Route::get('/tenants/{tenant}/edit', [App\Http\Controllers\TenantsController::class, 'edit'])
    ->middleware(['auth'])
    ->name('tenants.edit');

Route::post('/tenants', [App\Http\Controllers\TenantsController::class, 'store'])
    ->middleware(['auth'])
    ->name('tenants.store');

Route::put('/tenants/{tenant}', [App\Http\Controllers\TenantsController::class, 'update'])
    ->middleware(['auth'])
    ->name('tenants.update');

Route::delete('/tenants/{tenant}', [App\Http\Controllers\TenantsController::class, 'destroy'])
    ->middleware(['auth'])
    ->name('tenants.destroy');

Route::get('/tenants/{tenant}/payments', function ($id) {
    return Inertia::render('Tenants/Payments', ['id' => $id]);
})->middleware(['auth'])->name('tenants.payments');

Route::get('/invoices', function () {
    return Inertia::render('Invoices/Index');
})->name('invoices.index');

Route::get('/invoices/create', function () {
    return Inertia::render('Invoices/Create');
})->middleware(['auth'])->name('invoices.create');

Route::get('/invoices/{invoice}', function ($id) {
    return Inertia::render('Invoices/Show', ['id' => $id]);
})->middleware(['auth'])->name('invoices.show');

Route::get('/invoices/{invoice}/edit', function ($id) {
    return Inertia::render('Invoices/Edit', ['id' => $id]);
})->middleware(['auth'])->name('invoices.edit');

Route::get('/leads', function () {
    return Inertia::render('Leads/Index');
})->name('leads.index');

Route::get('/leads/create', function () {
    return Inertia::render('Leads/Create');
})->middleware(['auth'])->name('leads.create');

Route::get('/leads/{lead}', function ($id) {
    return Inertia::render('Leads/Show', ['id' => $id]);
})->middleware(['auth'])->name('leads.show');

Route::get('/leads/{lead}/edit', function ($id) {
    return Inertia::render('Leads/Edit', ['id' => $id]);
})->middleware(['auth'])->name('leads.edit');

// Expense Management Routes
Route::get('/expenses', function () {
    return Inertia::render('Expenses/Index');
})->middleware(['auth'])->name('expenses.index');

Route::get('/expenses/create', function () {
    return Inertia::render('Expenses/Create');
})->middleware(['auth'])->name('expenses.create');

Route::get('/expenses/{expense}', function ($id) {
    return Inertia::render('Expenses/Show', ['id' => $id]);
})->middleware(['auth'])->name('expenses.show');

Route::get('/expenses/{expense}/edit', function ($id) {
    return Inertia::render('Expenses/Edit', ['id' => $id]);
})->middleware(['auth'])->name('expenses.edit');

// Document Management Routes
Route::get('/documents', function () {
    return Inertia::render('Documents/Index');
})->middleware(['auth'])->name('documents.index');

Route::get('/documents/create', function () {
    return Inertia::render('Documents/Create');
})->middleware(['auth'])->name('documents.create');

Route::get('/documents/{document}', function ($id) {
    return Inertia::render('Documents/Show', ['id' => $id]);
})->middleware(['auth'])->name('documents.show');

Route::get('/documents/{document}/edit', function ($id) {
    return Inertia::render('Documents/Edit', ['id' => $id]);
})->middleware(['auth'])->name('documents.edit');

// Public Marketplace Routes (moved to top)

Route::get('/marketplace', function () {
    return Inertia::render('Marketplace/Search');
})->name('marketplace.search');

Route::get('/marketplace/properties/{id}', function ($id) {
    return Inertia::render('Marketplace/PropertyShow', ['id' => $id]);
})->name('marketplace.property.show');

// Property Comparison Route
Route::get('/compare', function () {
    return Inertia::render('Compare/Index');
})->name('compare.index');

// Advanced Analytics Dashboard
Route::get('/analytics', function () {
    return Inertia::render('Analytics/Dashboard');
})->middleware(['auth'])->name('analytics.dashboard');

// Admin routes
Route::prefix('admin')->middleware(['auth', 'role:super_admin'])->group(function () {
    Route::get('/', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('admin.dashboard');
    
    Route::get('/organizations', function () {
        return Inertia::render('Admin/Organizations');
    })->name('admin.organizations');
    
    Route::get('/users', function () {
        return Inertia::render('Admin/Users');
    })->name('admin.users');
    
    Route::get('/plans', function () {
        return Inertia::render('Admin/Plans');
    })->name('admin.plans');
    
    Route::get('/analytics', function () {
        return Inertia::render('Admin/Analytics');
    })->name('admin.analytics');
});

// Frontend User Routes
Route::prefix('user')->group(function () {
    // Authentication Routes
    Route::middleware('guest')->group(function () {
        Route::get('register', [App\Http\Controllers\Auth\FrontendAuthController::class, 'showRegister'])->name('user.register');
        Route::post('register', [App\Http\Controllers\Auth\FrontendAuthController::class, 'register']);
        Route::get('login', [App\Http\Controllers\Auth\FrontendAuthController::class, 'showLogin'])->name('user.login');
        Route::post('login', [App\Http\Controllers\Auth\FrontendAuthController::class, 'login']);
    });
    
    // Protected Routes
    Route::middleware('auth')->group(function () {
        Route::post('logout', [App\Http\Controllers\Auth\FrontendAuthController::class, 'logout'])->name('user.logout');
        Route::get('dashboard', function () {
            return Inertia::render('User/Dashboard');
        })->name('user.dashboard');
        Route::get('favorites', function () {
            return Inertia::render('User/Favorites');
        })->name('user.favorites');
        
        // Favorites API Routes (for session-based authentication)
        Route::prefix('api/v1/user/favorites')->middleware(['auth:sanctum,web'])->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\V1\FavoritesController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Api\V1\FavoritesController::class, 'store']);
            Route::delete('/{propertyId}', [\App\Http\Controllers\Api\V1\FavoritesController::class, 'destroy']);
            Route::post('/toggle', [\App\Http\Controllers\Api\V1\FavoritesController::class, 'toggle']);
            Route::get('/check/{propertyId}', [\App\Http\Controllers\Api\V1\FavoritesController::class, 'check']);
            Route::get('/count', [\App\Http\Controllers\Api\V1\FavoritesController::class, 'count']);
        });
        
        // User Dashboard API Route
        Route::get('api/v1/user/dashboard', function (Request $request) {
            $user = $request->user();
            
            // Only allow frontend users
            if ($user->user_type !== 'frontend') {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }
            
            // Get real favorites count
            $favoritesCount = $user->favorites()->count();
            
            // Get recent favorites (last 3)
            $recentFavorites = $user->favoriteProperties()
                ->with(['organization', 'media'])
                ->latest('favorites.created_at')
                ->limit(3)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => [
                        'favorites' => $favoritesCount,
                        'inquiries' => 2, // TODO: Implement inquiries count
                        'saved_searches' => 1, // TODO: Implement saved searches count
                        'recent_views' => 12 // TODO: Implement recent views count
                    ],
                    'recent_favorites' => $recentFavorites,
                    'recent_inquiries' => [] // TODO: Implement recent inquiries
                ]
            ]);
        });
    });
});

require __DIR__.'/auth.php';