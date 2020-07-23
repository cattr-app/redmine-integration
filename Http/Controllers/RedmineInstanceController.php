<?php

namespace Modules\RedmineIntegration\Http\Controllers;

use Modules\RedmineIntegration\Entities\RedmineInstance;
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

    public function create(UpdateRedmineInstance $request)
    {
        $item = $this->redmineInstanceService->create($request->validated());

        return response()->json($item);
    }

    public function update(UpdateRedmineInstance $request, $id)
    {
        /** @var RedmineInstance $item */
        $item = RedmineInstance::find($id);
        $item->fill($request->validated());

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
