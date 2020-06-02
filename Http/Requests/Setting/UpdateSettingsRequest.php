<?php

namespace Modules\RedmineIntegration\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Determine if user authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'enabled' => 'sometimes|required|bool',
            'url' => 'sometimes|required|url',
            'api_key' => 'sometimes|required|string',
            //'priorities' => 'sometimes|required|json',
            //'statuses' => 'sometimes|required|json',
            'sync' => 'sometimes|required|bool'
        ];
    }
}
