<?php

namespace Modules\RedmineIntegration\Http\Requests;

use App\Http\Requests\CattrFormRequest;
use App\Models\Project;

class CreateRedmineLinkedProject extends CattrFormRequest
{
    /**
     * Creating a linked project creates a core project, so require the same
     * permission as the core project creation endpoint.
     */
    protected function _authorize(): bool
    {
        return $this->user()->can('create', Project::class);
    }

    /**
     * The project source is set by the server and is never accepted here.
     */
    protected function _rules(): array
    {
        return [
            'name' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
        ];
    }
}
