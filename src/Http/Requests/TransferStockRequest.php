<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_id'              => ['required', 'string'],
            'quantity'             => ['required', 'numeric', 'min:0.0001'],
            'from_warehouse_id'    => ['required', 'string'],
            'to_warehouse_id'      => ['required', 'string'],
            'from_location_id'     => ['nullable', 'string'],
            'to_location_id'       => ['nullable', 'string'],
            'batch_code'           => ['nullable', 'string', 'max:100'],
            'serial_codes'         => ['nullable', 'array'],
            'serial_codes.*'       => ['string', 'max:150'],
            'reference_doc_number' => ['nullable', 'string', 'max:100'],
            'notes'                => ['nullable', 'string', 'max:2000'],
            'idempotency_key'      => ['nullable', 'string', 'max:128'],
            'metadata'             => ['nullable', 'array'],
        ];
    }
}
