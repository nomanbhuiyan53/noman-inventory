<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:255'],
            'code'             => ['required', 'string', 'max:100'],
            'item_type_id'     => ['nullable', 'string'],
            'category_id'      => ['nullable', 'string'],
            'unit_id'          => ['nullable', 'string'],
            'sku'              => ['nullable', 'string', 'max:100'],
            'barcode'          => ['nullable', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'brand'            => ['nullable', 'string', 'max:100'],
            'standard_cost'    => ['nullable', 'numeric', 'min:0'],
            'selling_price'    => ['nullable', 'numeric', 'min:0'],
            'currency'         => ['nullable', 'string', 'size:3'],
            'reorder_level'    => ['nullable', 'numeric', 'min:0'],
            'reorder_quantity' => ['nullable', 'numeric', 'min:0'],
            'industry_profile' => ['nullable', 'string'],
            'policy_overrides' => ['nullable', 'array'],
            'is_active'        => ['nullable', 'boolean'],
            'is_purchasable'   => ['nullable', 'boolean'],
            'is_saleable'      => ['nullable', 'boolean'],
            'is_stockable'     => ['nullable', 'boolean'],
            'metadata'         => ['nullable', 'array'],
        ];
    }
}
