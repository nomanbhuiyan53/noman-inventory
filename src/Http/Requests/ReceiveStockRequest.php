<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReceiveStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization is handled via policies registered in the host app
    }

    public function rules(): array
    {
        return [
            'item_id'                => ['required', 'string'],
            'quantity'               => ['required', 'numeric', 'min:0.0001'],
            'warehouse_id'           => ['required', 'string'],
            'location_id'            => ['nullable', 'string'],
            'unit_cost'              => ['nullable', 'numeric', 'min:0'],
            'currency'               => ['nullable', 'string', 'size:3'],
            'batch_code'             => ['nullable', 'string', 'max:100'],
            'expiry_date'            => ['nullable', 'date_format:Y-m-d'],
            'serial_codes'           => ['nullable', 'array'],
            'serial_codes.*'         => ['string', 'max:150'],
            'reference_doc_number'   => ['nullable', 'string', 'max:100'],
            'notes'                  => ['nullable', 'string', 'max:2000'],
            'idempotency_key'        => ['nullable', 'string', 'max:128'],
            'metadata'               => ['nullable', 'array'],
        ];
    }
}
