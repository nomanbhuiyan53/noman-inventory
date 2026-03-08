<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Support;

use Noman\Inventory\Contracts\TenantResolverContract;
use Noman\Inventory\Domain\Shared\ValueObjects\TenantId;

/**
 * Default (null) tenant resolver for single-tenant installations.
 *
 * Always returns null, meaning all inventory queries and operations
 * run without any tenant scoping. This is the default binding and
 * is suitable for applications that do not require multi-tenancy.
 *
 * To enable multi-tenancy, bind your own TenantResolverContract
 * implementation in your application service provider.
 */
final class NullTenantResolver implements TenantResolverContract
{
    public function getCurrentTenantId(): ?TenantId
    {
        return null;
    }

    public function hasTenant(): bool
    {
        return false;
    }
}
