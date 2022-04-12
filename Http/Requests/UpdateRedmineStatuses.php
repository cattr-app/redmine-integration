<?php

namespace Modules\RedmineIntegration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRedmineStatuses extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'statuses' => 'required|array',
            'statuses.*.id' => 'required|integer',
            'statuses.*.status_id' => 'required|integer',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }
}
