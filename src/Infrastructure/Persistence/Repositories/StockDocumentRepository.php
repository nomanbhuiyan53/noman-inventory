<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Persistence\Repositories;

use Noman\Inventory\Domain\Shared\Enums\DocumentStatus;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockDocument;

/**
 * Repository for stock document persistence.
 */
class StockDocumentRepository
{
    public function findById(string $documentId, ?string $tenantId = null): ?InventoryStockDocument
    {
        return InventoryStockDocument::query()
            ->forTenant($tenantId)
            ->with(['lines', 'lines.item', 'lines.batch'])
            ->find($documentId);
    }

    public function findByNumber(
        string $documentNumber,
        ?string $tenantId = null,
    ): ?InventoryStockDocument {
        return InventoryStockDocument::query()
            ->forTenant($tenantId)
            ->where('document_number', $documentNumber)
            ->first();
    }

    public function findByIdempotencyKey(
        string $idempotencyKey,
        ?string $tenantId = null,
    ): ?InventoryStockDocument {
        return InventoryStockDocument::query()
            ->forTenant($tenantId)
            ->where('idempotency_key', $idempotencyKey)
            ->first();
    }

    /**
     * @return InventoryStockDocument[]
     */
    public function findByStatus(
        DocumentStatus $status,
        ?string $tenantId = null,
        int $limit = 50,
    ): array {
        return InventoryStockDocument::query()
            ->forTenant($tenantId)
            ->where('status', $status->value)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->all();
    }

    public function save(InventoryStockDocument $document): InventoryStockDocument
    {
        $document->save();

        return $document;
    }
}
