<?php

namespace Modules\RedmineIntegration\Http\Requests;

use App\Http\Requests\CattrFormRequest;

class CreateRedmineUser extends CattrFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function _rules(): array
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
    protected function _authorize(): bool
    {
        return true;
    }
}
