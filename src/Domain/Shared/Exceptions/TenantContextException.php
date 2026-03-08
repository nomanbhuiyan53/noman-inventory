<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Shared\Exceptions;

/**
 * Thrown when a tenant context is required but not available.
 */
class TenantContextException extends InventoryException
{
    public static function missingContext(): self
    {
        return new self(
            'A tenant context is required but none could be resolved. '
            . 'Ensure a TenantResolverContract implementation is bound and the current tenant is set.'
        );
    }
}
