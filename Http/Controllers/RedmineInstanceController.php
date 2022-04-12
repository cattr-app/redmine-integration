<?php

namespace Modules\RedmineIntegration\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Http\Requests\CreateRedmineInstance;
use Modules\RedmineIntegration\Http\Requests\UpdateRedmineInstance;
use Modules\RedmineIntegration\Services\RedmineInstanceService;

class RedmineInstanceController extends AbstractRedmineController
{
    public function __construct(protected RedmineInstanceService $redmineInstanceService)
    {
    }

    public static function getControllerRules(): array
    {
        return [
            'list' => 'integration.redmine',
            'show' => 'integration.redmine',
        ];
    }

    public function list(): JsonResponse
    {
        $items = RedmineInstance::all();

        return response()->json($items);
    }

    public function show($id): JsonResponse
    {
        /** @var RedmineInstance $item */
        $item = RedmineInstance::find($id);

        return response()->json($item);
    }

    public function create(CreateRedmineInstance $request): JsonResponse
    {
        $item = $this->redmineInstanceService->create($request->validated());

        return response()->json($item);
    }

    public function update(UpdateRedmineInstance $request, $id): JsonResponse
    {
        $item = $this->redmineInstanceService->update($id, $request->validated());

        return response()->json($item);
    }

    public function delete($id): JsonResponse
    {
        /** @var RedmineInstance $item */
        $item = RedmineInstance::find($id);
        $item->delete();

        return response()->json($item);
    }
}
