<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TenantScope
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->org_id) {
            // Set global scope for all models that have org_id
            $this->applyTenantScope(auth()->user()->org_id);
        }

        return $next($request);
    }

    /**
     * Apply tenant scope to models
     */
    private function applyTenantScope(int $orgId): void
    {
        // List of models that should be scoped by org_id
        $scopedModels = [
            \App\Models\Property::class,
            \App\Models\Tenant::class,
            \App\Models\Invoice::class,
            \App\Models\Payment::class,
            \App\Models\Lead::class,
            \App\Models\Expense::class,
            \App\Models\Subscription::class,
            \App\Models\Document::class,
            \App\Models\Notification::class,
        ];

        foreach ($scopedModels as $model) {
            $model::addGlobalScope('tenant', function (Builder $builder) use ($orgId) {
                $builder->where('org_id', $orgId);
            });
        }
    }
}
