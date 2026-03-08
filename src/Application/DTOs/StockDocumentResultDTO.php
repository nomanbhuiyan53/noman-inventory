<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\DTOs;

use Noman\Inventory\Domain\Shared\Enums\DocumentStatus;

/**
 * Result DTO returned from all stock document operations.
 *
 * Provides the caller with enough information to:
 *  - Display confirmation to the user
 *  - Follow up with reporting queries
 *  - Log or audit the operation
 */
final class StockDocumentResultDTO
{
    /**
     * @param  string          $documentId      UUID/ULID of the created/updated document
     * @param  string          $documentNumber  Human-readable document number (e.g. GRN-20241201-A3F2)
     * @param  DocumentStatus  $status          Final status of the document
     * @param  string          $documentType    Type of document (receive, issue, transfer, etc.)
     * @param  string|null     $tenantId        Tenant this document belongs to
     * @param  int             $lineCount       Number of document lines posted
     * @param  string|null     $reversalOf      If this is a reversal, the original document ID
     * @param  array           $movementIds     IDs of the created stock movements
     */
    public function __construct(
        public readonly string $documentId,
        public readonly string $documentNumber,
        public readonly DocumentStatus $status,
        public readonly string $documentType,
        public readonly ?string $tenantId,
        public readonly int $lineCount,
        public readonly ?string $reversalOf = null,
        public readonly array $movementIds = [],
    ) {}

    public function isPosted(): bool
    {
        return $this->status === DocumentStatus::Posted;
    }

    public function isPendingApproval(): bool
    {
        return $this->status === DocumentStatus::Pending;
    }
}
