<?php

namespace Modules\RedmineIntegration\Http\Controllers;

use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Http\Requests\CreateRedmineInstance;
use Modules\RedmineIntegration\Http\Requests\UpdateRedmineInstance;
use Modules\RedmineIntegration\Services\RedmineInstanceService;

class RedmineInstanceController extends AbstractRedmineController
{
    protected RedmineInstanceService $redmineInstanceService;

    public function __construct(RedmineInstanceService $redmineInstanceService)
    {
        parent::__construct();

        $this->redmineInstanceService = $redmineInstanceService;
    }

    public static function getControllerRules(): array
    {
        return [
            'list' => 'integration.redmine',
            'show' => 'integration.redmine',
        ];
    }

    public function list()
    {
        $items = RedmineInstance::all();

        return response()->json($items);
    }

    public function show($id)
    {
        /** @var RedmineInstance $item */
        $item = RedmineInstance::find($id);

        return response()->json($item);
    }

    public function create(CreateRedmineInstance $request)
    {
        $item = $this->redmineInstanceService->create($request->validated());

        return response()->json($item);
    }

    public function update(UpdateRedmineInstance $request, $id)
    {
        /** @var RedmineInstance $item */
        $item = RedmineInstance::find($id);
        $item->fill($request->validated());
        $item->save();

        return response()->json($item);
    }

    public function delete($id)
    {
        /** @var RedmineInstance $item */
        $item = RedmineInstance::find($id);
        $item->delete();

        return response()->json($item);
    }
}
