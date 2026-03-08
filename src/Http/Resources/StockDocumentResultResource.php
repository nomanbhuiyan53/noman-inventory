<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Noman\Inventory\Application\DTOs\StockDocumentResultDTO;

/**
 * API resource for the StockDocumentResultDTO returned by posting actions.
 */
class StockDocumentResultResource extends JsonResource
{
    public function __construct(private readonly StockDocumentResultDTO $dto) {}

    public function toArray(Request $request): array
    {
        return [
            'document_id'     => $this->dto->documentId,
            'document_number' => $this->dto->documentNumber,
            'status'          => $this->dto->status->value,
            'status_label'    => $this->dto->status->label(),
            'document_type'   => $this->dto->documentType,
            'tenant_id'       => $this->dto->tenantId,
            'line_count'      => $this->dto->lineCount,
            'reversal_of'     => $this->dto->reversalOf,
            'movement_ids'    => $this->dto->movementIds,
            'posted'          => $this->dto->isPosted(),
            'pending_approval'=> $this->dto->isPendingApproval(),
        ];
    }
}
