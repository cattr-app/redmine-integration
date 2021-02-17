<?php

namespace Modules\RedmineIntegration\Http\Controllers;

use Modules\RedmineIntegration\Entities\RedmineStatus;
use Modules\RedmineIntegration\Http\Requests\UpdateRedmineStatus;
use Modules\RedmineIntegration\Http\Requests\UpdateRedmineStatuses;
use Modules\RedmineIntegration\Services\RedmineStatusService;

class RedmineStatusController extends AbstractRedmineController
{
    protected RedmineStatusService $redmineStatusService;

    public function __construct(RedmineStatusService $redmineStatusService)
    {
        parent::__construct();

        $this->redmineInstanceService = $redmineStatusService;
    }

    public static function getControllerRules(): array
    {
        return [
            'list' => 'integration.redmine',
            'show' => 'integration.redmine',
        ];
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

    public function bulkUpdate(UpdateRedmineStatuses $request, $instanceId)
    {
        $input = $request->validated();
        $items = [];

        foreach ($input['statuses'] as $status) {
            /** @var RedmineStatus $item */
            $item = RedmineStatus::where([
                'redmine_instance_id' => $instanceId,
                'id'                  => $status['id'],
            ])->firstOrFail();

            $item->status_id = $status['status_id'];
            $item->save();

            $items[] = $item;
        }

        return response()->json($items);
    }
}
