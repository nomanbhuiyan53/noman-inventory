<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                        => $this->id,
            'document_number'           => $this->document_number,
            'document_type'             => $this->document_type,
            'status'                    => $this->status?->value,
            'status_label'              => $this->status?->label(),
            'source_warehouse_id'       => $this->source_warehouse_id,
            'destination_warehouse_id'  => $this->destination_warehouse_id,
            'reference_document_number' => $this->reference_document_number,
            'reference_type'            => $this->reference_type,
            'reference_id'              => $this->reference_id,
            'reversal_of_id'            => $this->reversal_of_id,
            'reversal_reason'           => $this->reversal_reason,
            'notes'                     => $this->notes,
            'tenant_id'                 => $this->tenant_id,
            'posted_at'                 => $this->posted_at?->toISOString(),
            'approved_at'               => $this->approved_at?->toISOString(),
            'reversed_at'               => $this->reversed_at?->toISOString(),
            'created_at'                => $this->created_at?->toISOString(),

            'lines' => $this->whenLoaded('lines', fn () =>
                $this->lines->map(fn ($line) => [
                    'id'          => $line->id,
                    'item_id'     => $line->item_id,
                    'item_code'   => $line->item?->code,
                    'item_name'   => $line->item?->name,
                    'quantity'    => $line->quantity,
                    'unit_cost'   => $line->unit_cost,
                    'total_cost'  => $line->total_cost,
                    'currency'    => $line->currency,
                    'warehouse_id'=> $line->warehouse_id,
                    'location_id' => $line->location_id,
                    'batch_id'    => $line->batch_id,
                    'batch_code'  => $line->batch?->batch_code,
                    'notes'       => $line->notes,
                ])->all()
            ),
        ];
    }
}
