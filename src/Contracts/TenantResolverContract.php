<?php

declare(strict_types=1);

namespace Noman\Inventory\Contracts;

use Noman\Inventory\Domain\Shared\ValueObjects\TenantId;

/**
 * Contract for resolving the current tenant context.
 *
 * Implement this interface in your host application and bind it in your
 * service provider to integrate with your tenancy solution (Tenancy for Laravel,
 * Spatie Multitenancy, custom middleware-based, etc.).
 *
 * All inventory services resolve tenant context through this contract, so no
 * tenancy package is hardcoded in the inventory engine.
 *
 * Example host application binding:
 *
 *   $this->app->bind(
 *       TenantResolverContract::class,
 *       MyApp\Inventory\CurrentTenantResolver::class
 *   );
 */
interface TenantResolverContract
{
    /**
     * Returns the TenantId for the currently active tenant.
     * Returns null in single-tenant mode or when no tenant context is set.
     */
    public function getCurrentTenantId(): ?TenantId;

    /**
     * Returns true if there is an active tenant context.
     */
    public function hasTenant(): bool;
}
