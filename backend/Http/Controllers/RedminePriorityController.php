<?php

namespace Modules\RedmineIntegration\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\RedmineIntegration\Entities\RedminePriority;
use Modules\RedmineIntegration\Http\Requests\UpdateRedminePriorities;
use Modules\RedmineIntegration\Http\Requests\UpdateRedminePriority;
use Modules\RedmineIntegration\Services\RedminePriorityService;

class RedminePriorityController extends AbstractRedmineController
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
        $items = RedminePriority::where(['redmine_instance_id' => $instanceId])->get();

        return response()->json($items);
    }

    public function show($instanceId, $id): JsonResponse
    {
        /** @var RedminePriority $item */
        $item = RedminePriority::where([
            'redmine_instance_id' => $instanceId,
            'id'                  => $id,
        ]);

        return response()->json($item);
    }

    public function update(UpdateRedminePriority $request, $instanceId, $id): JsonResponse
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

    public function bulkUpdate(UpdateRedminePriorities $request, $instanceId): JsonResponse
    {
        $input = $request->validated();
        $items = [];

        foreach ($input['priorities'] as $priority) {
            /** @var RedminePriority $item */
            $item = RedminePriority::where([
                'redmine_instance_id' => $instanceId,
                'id'                  => $priority['id'],
            ])->firstOrFail();

            $item->priority_id = $priority['priority_id'];
            $item->save();

            $items[] = $item;
        }

        return response()->json($items);
    }
}
