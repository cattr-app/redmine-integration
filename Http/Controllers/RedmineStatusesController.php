<?php

namespace Modules\RedmineIntegration\Http\Controllers;

use Modules\RedmineIntegration\Entities\RedmineStatus;
use Modules\RedmineIntegration\Http\Requests\UpdateRedmineStatus;
use Modules\RedmineIntegration\Services\RedmineStatusService;

class RedmineStatusesController extends AbstractRedmineController
{
    protected RedmineStatusService $redmineStatusService;

    public function __construct(RedmineStatusService $redmineStatusService)
    {
        parent::__construct();

        $this->redmineInstanceService = $redmineStatusService;
    }

    public function list($instanceId)
    {
        $items = RedmineStatus::where(['redmine_instance_id' => $instanceId])->get();

        return response()->json($items);
    }

    public function show($instanceId, $id)
    {
        /** @var RedmineStatus $item */
        $item = RedmineStatus::where([
            'redmine_instance_id' => $instanceId,
            'id'                  => $id,
        ]);

        return response()->json($item);
    }

    public function update(UpdateRedmineStatus $request, $id)
    {
        /** @var RedmineStatus $item */
        $item = RedmineStatus::find($id);
        $item->fill($request->validated());
        $item->save();

        return response()->json($item);
    }
}
