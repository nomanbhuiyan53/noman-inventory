<?php

declare(strict_types=1);

namespace Noman\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReverseDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:5', 'max:500'],
            'notes'  => ['nullable', 'string', 'max:2000'],
        ];
    }
}
