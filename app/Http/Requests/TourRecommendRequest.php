<?php

namespace App\Http\Requests;

use App\Models\Club;
use Illuminate\Foundation\Http\FormRequest;

class TourRecommendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'area'           => ['required', 'string', 'in:' . implode(',', Club::$areas)],
            'time'           => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'genre'          => ['nullable', 'array'],
            'genre.*'        => ['string', 'in:' . implode(',', Club::$genres)],
            'radius'         => ['nullable', 'integer', 'min:0', 'max:10'],
            'budget'         => ['required', 'integer', 'min:0', 'max:500000'],
            'foreigner_mode' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'area.required' => '지역을 선택해 주세요.',
            'area.in'       => '올바른 지역을 선택해 주세요.',
            'time.required' => '출발 시간을 선택해 주세요.',
            'budget.min'    => '예산은 최소 10,000원 이상이어야 합니다.',
        ];
    }
}
