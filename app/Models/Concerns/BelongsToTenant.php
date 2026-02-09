<?php

namespace App\Models\Concerns;

use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->runningInConsole()) {
                return;
            }

            $tenantId = app(TenantContext::class)->tenantId();

            if ($tenantId) {
                $builder->where($builder->qualifyColumn('tenant_id'), $tenantId);
            }
        });

        static::creating(function ($model): void {
            if (! $model->tenant_id) {
                $tenantId = app(TenantContext::class)->tenantId();

                if ($tenantId) {
                    $model->tenant_id = $tenantId;
                }
            }
        });
    }

    public function scopeForTenant(Builder $query, ?int $tenantId = null): Builder
    {
        $tenantId ??= app(TenantContext::class)->tenantId();

        if ($tenantId) {
            $query->where($query->qualifyColumn('tenant_id'), $tenantId);
        }

        return $query;
    }
}
