<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\Actions;

use Illuminate\Support\Str;
use Noman\Inventory\Contracts\DocumentNumberGeneratorContract;
use Noman\Inventory\Contracts\PolicyResolverContract;
use Noman\Inventory\Contracts\TenantResolverContract;
use Noman\Inventory\Domain\Shared\Exceptions\PolicyViolationException;
use Noman\Inventory\Domain\Shared\ValueObjects\InventoryPolicy;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockDocument;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockDocumentLine;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockMovement;

/**
 * Base class for all inventory action classes.
 *
 * Provides shared utilities for:
 *  - Resolving the current tenant ID
 *  - Generating document numbers
 *  - Validating item policy requirements
 *  - Creating stock document + line + movement records
 */
abstract class AbstractInventoryAction
{
    public function __construct(
        protected readonly TenantResolverContract $tenantResolver,
        protected readonly PolicyResolverContract $policyResolver,
        protected readonly DocumentNumberGeneratorContract $docNumberGenerator,
    ) {}

    // -------------------------------------------------------------------------
    // Tenant resolution
    // -------------------------------------------------------------------------

    protected function resolveTenantId(?string $explicitTenantId): ?string
    {
        if ($explicitTenantId !== null) {
            return $explicitTenantId;
        }

        return $this->tenantResolver->getCurrentTenantId()?->getValue();
    }

    // -------------------------------------------------------------------------
    // Policy validation
    // -------------------------------------------------------------------------

    /**
     * Validate that all required fields are present according to the resolved policy.
     *
     * @throws PolicyViolationException
     */
    protected function validatePolicy(
        InventoryPolicy $policy,
        string $itemId,
        ?string $batchCode,
        ?string $expiryDate,
        array $serialCodes,
        ?string $warehouseId,
    ): void {
        if ($policy->batchRequired && empty($batchCode)) {
            throw PolicyViolationException::batchRequired($itemId);
        }

        if ($policy->expiryRequired && empty($expiryDate)) {
            throw PolicyViolationException::expiryRequired($itemId);
        }

        if ($policy->serialRequired && empty($serialCodes)) {
            throw PolicyViolationException::serialRequired($itemId);
        }

        if ($policy->locationRequired && empty($warehouseId)) {
            throw PolicyViolationException::locationRequired($itemId);
        }
    }

    // -------------------------------------------------------------------------
    // Document creation helpers
    // -------------------------------------------------------------------------

    protected function createDocument(array $attributes): InventoryStockDocument
    {
        $document = new InventoryStockDocument();

        foreach ($attributes as $key => $value) {
            $document->{$key} = $value;
        }

        $document->save();

        return $document;
    }

    protected function createDocumentLine(
        InventoryStockDocument $document,
        array $attributes,
    ): InventoryStockDocumentLine {
        $line = new InventoryStockDocumentLine();
        $line->stock_document_id = $document->id;
        $line->tenant_id         = $document->tenant_id;

        foreach ($attributes as $key => $value) {
            $line->{$key} = $value;
        }

        $line->save();

        return $line;
    }

    protected function createMovement(
        InventoryStockDocument $document,
        InventoryStockDocumentLine $line,
        array $attributes,
    ): InventoryStockMovement {
        $movement = new InventoryStockMovement();
        $movement->stock_document_id      = $document->id;
        $movement->stock_document_line_id = $line->id;
        $movement->tenant_id              = $document->tenant_id;
        $movement->posted_at              = $document->posted_at ?? now();

        foreach ($attributes as $key => $value) {
            $movement->{$key} = $value;
        }

        $movement->save();

        return $movement;
    }

    protected function generateId(): string
    {
        return (string) Str::ulid();
    }
}
