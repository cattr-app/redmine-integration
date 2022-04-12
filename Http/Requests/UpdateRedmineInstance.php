<?php

namespace Modules\RedmineIntegration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRedmineInstance extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'url' => 'required|url|max:256',
            'sync_type' => 'required|string|max:32',
            'sync_new_projects' => 'required|boolean',
            'project_sync_interval' => 'required|integer',
            'task_sync_interval' => 'required|integer',
            'priority_sync_interval' => 'required|integer',
            'status_sync_interval' => 'required|integer',
            'auth_method' => 'sometimes|required|string',
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
