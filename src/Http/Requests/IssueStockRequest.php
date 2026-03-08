<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Noman\Inventory\Domain\Shared\Enums\MovementType;

class IssueStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $validTypes = implode(',', array_map(fn ($t) => $t->value, [
            MovementType::SaleOut,
            MovementType::ConsumptionOut,
            MovementType::WastageOut,
            MovementType::DeadStockOut,
            MovementType::ReturnOut,
            MovementType::ExpiredOut,
            MovementType::QuarantineIn,
        ]));

        return [
            'item_id'              => ['required', 'string'],
            'quantity'             => ['required', 'numeric', 'min:0.0001'],
            'warehouse_id'         => ['required', 'string'],
            'movement_type'        => ['required', 'string', "in:{$validTypes}"],
            'location_id'          => ['nullable', 'string'],
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
