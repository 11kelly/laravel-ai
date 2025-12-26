<?php

declare(strict_types=1);

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 前端创建预订请求
 */
final class CreateBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'participants_count' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // 在验证前进行类型转换
        $this->merge([
            'participants_count' => (int) $this->input('participants_count'),
        ]);
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'participants_count' => '参与人数',
            'notes' => '备注',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'participants_count.required' => '请输入参与人数',
            'participants_count.integer' => '参与人数必须是整数',
            'participants_count.min' => '参与人数至少为 1',
            'notes.max' => '备注不能超过 500 个字符',
        ];
    }
}

