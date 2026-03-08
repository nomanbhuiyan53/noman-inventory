<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Inventory\Events;

final class DocumentReversed
{
    public function __construct(
        public readonly string $originalDocumentId,
        public readonly string $reversalDocumentId,
        public readonly string $reversalDocumentNumber,
        public readonly string $reason,
        public readonly ?string $tenantId,
    ) {}
}
