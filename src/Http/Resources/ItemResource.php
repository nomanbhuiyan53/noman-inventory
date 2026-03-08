<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'code'             => $this->code,
            'sku'              => $this->sku,
            'barcode'          => $this->barcode,
            'barcode_type'     => $this->barcode_type,
            'brand'            => $this->brand,
            'description'      => $this->description,
            'item_type_id'     => $this->item_type_id,
            'category_id'      => $this->category_id,
            'unit_id'          => $this->unit_id,
            'standard_cost'    => $this->standard_cost,
            'selling_price'    => $this->selling_price,
            'currency'         => $this->currency,
            'reorder_level'    => $this->reorder_level,
            'reorder_quantity' => $this->reorder_quantity,
            'industry_profile' => $this->industry_profile?->value,
            'policy_overrides' => $this->policy_overrides,
            'is_active'        => $this->is_active,
            'is_purchasable'   => $this->is_purchasable,
            'is_saleable'      => $this->is_saleable,
            'is_stockable'     => $this->is_stockable,
            'metadata'         => $this->metadata,
            'tenant_id'        => $this->tenant_id,
            'created_at'       => $this->created_at?->toISOString(),
            'updated_at'       => $this->updated_at?->toISOString(),

            // Eager-loaded relationships
            'item_type'        => $this->whenLoaded('itemType', fn () => [
                'id'   => $this->itemType->id,
                'name' => $this->itemType->name,
                'code' => $this->itemType->code,
            ]),
            'category'         => $this->whenLoaded('category', fn () => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
            ]),
            'unit'             => $this->whenLoaded('unit', fn () => [
                'id'     => $this->unit->id,
                'name'   => $this->unit->name,
                'code'   => $this->unit->code,
                'symbol' => $this->unit->symbol,
            ]),
        ];
    }
}
