<?php

namespace Modules\RedmineIntegration\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\RedmineIntegration\Entities\RedmineStatus;
use Modules\RedmineIntegration\Http\Requests\UpdateRedmineStatus;
use Modules\RedmineIntegration\Http\Requests\UpdateRedmineStatuses;

class RedmineStatusController extends AbstractRedmineController
{
    public static function getControllerRules(): array
    {
        return [
            'list' => 'integration.redmine',
            'show' => 'integration.redmine',
        ];
    }

    public function list($instanceId): JsonResponse
    {
        $items = RedmineStatus::where(['redmine_instance_id' => $instanceId])->get();

        return response()->json($items);
    }

    public function show($instanceId, $id): JsonResponse
    {
        /** @var RedmineStatus $item */
        $item = RedmineStatus::where([
            'redmine_instance_id' => $instanceId,
            'id'                  => $id,
        ]);

        return response()->json($item);
    }

    public function update(UpdateRedmineStatus $request, $id): JsonResponse
    {
        /** @var RedmineStatus $item */
        $item = RedmineStatus::find($id);
        $item->fill($request->validated());
        $item->save();

        return response()->json($item);
    }

    public function bulkUpdate(UpdateRedmineStatuses $request, $instanceId): JsonResponse
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
