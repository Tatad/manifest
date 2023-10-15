<?php

namespace App\Http\Requests;

use App\Models\Manifest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManifestUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'images' => ['array'],
            'features' => ['string'],
        ];
    }
}
