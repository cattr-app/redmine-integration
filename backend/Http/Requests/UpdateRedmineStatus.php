<?php

namespace Modules\RedmineIntegration\Http\Requests;

use App\Http\Requests\CattrFormRequest;

class UpdateRedmineStatus extends CattrFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function _rules(): array
    {
        return [
            'status_id' => 'required|integer',
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
