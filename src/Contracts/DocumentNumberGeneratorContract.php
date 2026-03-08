<?php

declare(strict_types=1);

namespace Noman\Inventory\Contracts;

use Noman\Inventory\Domain\Shared\ValueObjects\DocumentNumber;

/**
 * Contract for generating unique, human-readable stock document numbers.
 *
 * The default implementation generates sequential or time-based numbers.
 * Host applications may replace this with database-sequence-backed,
 * configurable prefix, or financial-year-aware numbering.
 *
 * Example document types (lowercase, snake_case):
 *   grn (Goods Received Note), do (Delivery Order), sto (Stock Transfer Order),
 *   adj (Stock Adjustment), sc (Stock Count), rev (Reversal)
 */
interface DocumentNumberGeneratorContract
{
    /**
     * Generate a new unique document number for the given document type.
     *
     * @param string      $documentType  The document type identifier (e.g. 'grn', 'adj')
     * @param string|null $tenantId      Optional tenant ID to scope numbering per tenant
     */
    public function generate(string $documentType, ?string $tenantId = null): DocumentNumber;
}
