<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Inventory\Events;

final class DocumentPosted
{
    public function __construct(
        public readonly string $documentId,
        public readonly string $documentNumber,
        public readonly string $documentType,
        public readonly ?string $tenantId,
    ) {}
}
