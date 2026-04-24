<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommunityPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'type'     => ['required', 'in:review,tip,realtime'],
            'title'    => ['required', 'string', 'max:100'],
            'content'  => ['required', 'string', 'max:2000'],
            'club_id'  => ['nullable', 'exists:clubs,id'],
            'images'   => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'nickname.required' => '닉네임을 입력해 주세요.',
            'nickname.max'      => '닉네임은 20자 이내로 입력해 주세요.',
            'title.required'    => '제목을 입력해 주세요.',
            'content.required'  => '내용을 입력해 주세요.',
            'content.max'       => '내용은 2,000자 이내로 입력해 주세요.',
            'images.max'        => '이미지는 최대 5개까지 첨부할 수 있습니다.',
            'images.*.image'    => '이미지 파일만 업로드할 수 있습니다.',
            'images.*.mimes'    => 'JPG, PNG, WebP 형식만 지원합니다.',
            'images.*.max'      => '이미지 1개당 5MB 이하만 가능합니다.',
        ];
    }
}
