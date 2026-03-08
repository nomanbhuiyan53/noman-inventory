<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReserveStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_id'        => ['required', 'string'],
            'quantity'       => ['required', 'numeric', 'min:0.0001'],
            'warehouse_id'   => ['required', 'string'],
            'location_id'    => ['nullable', 'string'],
            'reference_type' => ['nullable', 'string', 'max:50'],
            'reference_id'   => ['nullable', 'string', 'max:64'],
            'expiry_minutes' => ['nullable', 'integer', 'min:1'],
            'notes'          => ['nullable', 'string', 'max:2000'],
            'metadata'       => ['nullable', 'array'],
        ];
    }
}
