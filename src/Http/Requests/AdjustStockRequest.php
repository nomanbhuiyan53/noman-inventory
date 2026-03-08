<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_id'              => ['required', 'string'],
            'quantity'             => ['required', 'numeric'],           // signed: + = in, - = out
            'warehouse_id'         => ['required', 'string'],
            'location_id'          => ['nullable', 'string'],
            'batch_code'           => ['nullable', 'string', 'max:100'],
            'reason'               => ['required', 'string', 'max:500'],
            'reference_doc_number' => ['nullable', 'string', 'max:100'],
            'notes'                => ['nullable', 'string', 'max:2000'],
            'idempotency_key'      => ['nullable', 'string', 'max:128'],
            'metadata'             => ['nullable', 'array'],
        ];
    }
}
