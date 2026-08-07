<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MunicipalitySaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('municipalities') && is_string($this->municipalities)) {
            $decoded = json_decode($this->municipalities, true);

            // Reemplace
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->merge([
                    'municipalities' => $decoded,
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'municipalities' => ['required', 'array'],
            'municipalities.*.cve_ent' => ['required'],
            'municipalities.*.nomgeo' => ['required'],
            'municipalities.*.pob_total' => ['nullable'],
        ];
    }
}
