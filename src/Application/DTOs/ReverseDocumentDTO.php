<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\DTOs;

/**
 * Data Transfer Object for reversing a posted stock document.
 *
 * Reversing creates compensating ledger entries that exactly negate
 * the original document's movements. The original document is marked
 * as 'reversed'; no existing rows are modified or deleted.
 */
final class ReverseDocumentDTO
{
    /**
     * @param  string  $documentId      UUID/ULID of the posted document to reverse
     * @param  string  $reason          Mandatory reason for the reversal (audit trail)
     * @param  string|null  $tenantId   Tenant scope
     * @param  string|null  $notes      Additional free-text notes
     */
    public function __construct(
        public readonly string $documentId,
        public readonly string $reason,
        public readonly ?string $tenantId = null,
        public readonly ?string $notes = null,
    ) {}
}
