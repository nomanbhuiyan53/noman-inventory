<?php

declare(strict_types=1);

namespace Noman\Inventory\Infrastructure\Support;

use Noman\Inventory\Contracts\DocumentNumberGeneratorContract;
use Noman\Inventory\Domain\Shared\ValueObjects\DocumentNumber;

/**
 * Default document number generator.
 *
 * Produces numbers in the format: {PREFIX}-{YYYYMMDD}-{RANDOM_HEX}
 * Example: GRN-20241201-A3F2B1
 *
 * For sequential numbering (e.g. GRN-2024-00001), replace this class
 * with a database-backed implementation and bind it in your service provider:
 *
 *   config(['inventory.bindings.document_number_generator' => MySequentialGenerator::class]);
 *
 * The prefix is derived from the document type string (first 3 chars, uppercased).
 * Custom prefix mappings can be added by extending this class.
 */
final class DefaultDocumentNumberGenerator implements DocumentNumberGeneratorContract
{
    /**
     * Maps document type keys to short prefix codes.
     */
    private const PREFIX_MAP = [
        'receive'       => 'GRN',
        'grn'           => 'GRN',
        'issue'         => 'DO',
        'do'            => 'DO',
        'transfer'      => 'STO',
        'sto'           => 'STO',
        'adjustment'    => 'ADJ',
        'adj'           => 'ADJ',
        'opening'       => 'OPB',
        'reversal'      => 'REV',
        'rev'           => 'REV',
        'stock_count'   => 'SCC',
        'scc'           => 'SCC',
        'reservation'   => 'RSV',
        'rsv'           => 'RSV',
    ];

    public function generate(string $documentType, ?string $tenantId = null): DocumentNumber
    {
        $prefix    = $this->resolvePrefix($documentType);
        $date      = now()->format('Ymd');
        $random    = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $tenantSeg = $tenantId ? strtoupper(substr($tenantId, 0, 4)) . '-' : '';

        return new DocumentNumber("{$prefix}-{$tenantSeg}{$date}-{$random}");
    }

    private function resolvePrefix(string $documentType): string
    {
        $lower = strtolower(trim($documentType));

        return self::PREFIX_MAP[$lower]
            ?? strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $documentType), 0, 3));
    }
}
