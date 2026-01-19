<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncObligationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'from_date' => 'nullable|date|before_or_equal:to_date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'business_id' => 'nullable|string|exists:hmrc_businesses,business_id'
        ];
    }
}

