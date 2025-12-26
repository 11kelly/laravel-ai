<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // 简单的管理员权限检查（生产环境应使用更完善的RBAC系统）
        $user = $this->user();
        return $user && $user->id === 1; // 假设ID为1的用户是管理员
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255|min:3',
            'description' => 'required|string|max:5000|min:10',
            'price' => 'nullable|numeric|min:0|max:999999.99',
            'capacity' => 'required|integer|min:1|max:10000',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after:start_date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => '活动标题不能为空',
            'title.min' => '活动标题至少需要3个字符',
            'title.max' => '活动标题不能超过255个字符',
            'description.required' => '活动描述不能为空',
            'description.min' => '活动描述至少需要10个字符',
            'description.max' => '活动描述不能超过5000个字符',
            'price.numeric' => '价格必须是数字',
            'price.min' => '价格不能为负数',
            'price.max' => '价格不能超过999999.99',
            'capacity.required' => '活动容量不能为空',
            'capacity.integer' => '活动容量必须是整数',
            'capacity.min' => '活动容量至少为1',
            'capacity.max' => '活动容量不能超过10000',
            'start_date.required' => '开始时间不能为空',
            'start_date.date' => '开始时间格式不正确',
            'start_date.after' => '开始时间必须在当前时间之后',
            'end_date.required' => '结束时间不能为空',
            'end_date.date' => '结束时间格式不正确',
            'end_date.after' => '结束时间必须在开始时间之后',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');

            if ($startDate && $endDate) {
                $start = strtotime($startDate);
                $end = strtotime($endDate);

                // 检查时间间隔是否合理（最大30天）
                $daysDiff = ($end - $start) / (60 * 60 * 24);
                if ($daysDiff > 30) {
                    $validator->errors()->add('end_date', '活动持续时间不能超过30天');
                }

                // 检查是否在合理的时间范围内（未来一年内）
                $oneYearFromNow = strtotime('+1 year');
                if ($end > $oneYearFromNow) {
                    $validator->errors()->add('end_date', '活动结束时间不能超过一年后');
                }
            }
        });
    }
}
