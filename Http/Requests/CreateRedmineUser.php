<?php

namespace Modules\RedmineIntegration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRedmineUser extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'redmine_instance_id' => 'required|integer|exists:redmine_instances,id',
            'auth_method' => 'required|string',
            'auth_login' => 'required_if:auth_method,basic|string',
            'auth_password' => 'required_if:auth_method,basic|string',
            'auth_api_key' => 'required_if:auth_method,api_key|string|max:256',
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
