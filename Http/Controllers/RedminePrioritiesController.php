<?php

namespace Modules\RedmineIntegration\Http\Controllers;

use Modules\RedmineIntegration\Entities\RedminePriority;
use Modules\RedmineIntegration\Http\Requests\UpdateRedminePriority;
use Modules\RedmineIntegration\Services\RedminePriorityService;

class RedminePrioritiesController extends AbstractRedmineController
{
    protected RedminePriorityService $redmineStatusService;

    public function __construct(RedminePriorityService $redmineStatusService)
    {
        parent::__construct();

        $this->redmineInstanceService = $redmineStatusService;
    }

    public function list($instanceId)
    {
        $items = RedminePriority::where(['redmine_instance_id' => $instanceId])->get();

        return response()->json($items);
    }

    public function show($instanceId, $id)
    {
        /** @var RedminePriority $item */
        $item = RedminePriority::where([
            'redmine_instance_id' => $instanceId,
            'id'                  => $id,
        ]);

        return response()->json($item);
    }

    public function update(UpdateRedminePriority $request, $instanceId, $id)
    {
        /** @var RedminePriority $item */
        $item = RedminePriority::where([
            'redmine_instance_id' => $instanceId,
            'id'                  => $id,
        ])->firstOrFail();

        $item->fill($request->validated());
        $item->save();

        return response()->json($item);
    }
}
