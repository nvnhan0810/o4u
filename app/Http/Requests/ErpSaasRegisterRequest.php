<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\ErpSaasRegistration\ErpSaasRegisterModules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ErpSaasRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $captchaRequired = ! (bool) config('services.turnstile.bypass');

        return [
            'company_name' => ['required', 'string', 'min:2', 'max:128'],
            'contact_name' => ['required', 'string', 'min:1', 'max:128'],
            'email' => ['required', 'email', 'max:128'],
            'phone' => ['required', 'string', 'min:8', 'max:32'],
            'modules' => [
                'required',
                'array',
                'min:'.ErpSaasRegisterModules::MIN,
                'max:'.ErpSaasRegisterModules::MAX,
            ],
            'modules.*' => [
                'required',
                'string',
                Rule::in(ErpSaasRegisterModules::codes()),
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
            'cf-turnstile-response' => [$captchaRequired ? 'required' : 'nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'company_name.required' => 'Vui lòng nhập tên công ty.',
            'contact_name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'modules.required' => 'Vui lòng chọn ít nhất 1 module.',
            'modules.min' => 'Vui lòng chọn ít nhất 1 module.',
            'modules.max' => 'Chỉ được chọn tối đa '.ErpSaasRegisterModules::MAX.' module.',
            'modules.*.in' => 'Module không hợp lệ.',
            'cf-turnstile-response.required' => 'Vui lòng xác minh captcha.',
        ];
    }
}
