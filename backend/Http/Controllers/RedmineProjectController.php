<?php

namespace Modules\RedmineIntegration\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Modules\RedmineIntegration\Entities\RedmineProject;
use Modules\RedmineIntegration\Http\Requests\UpdateRedmineProject;

class RedmineProjectController extends AbstractRedmineController
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
        $items = RedmineProject::where(['redmine_instance_id' => $instanceId])->get();

        return response()->json($items);
    }

    public function show($instanceId, $id): JsonResponse
    {
        /** @var RedmineProject $item */
        $item = RedmineProject::where([
            'redmine_instance_id' => $instanceId,
            'id'                  => $id,
        ]);

        return response()->json($item);
    }

    public function update(UpdateRedmineProject $request, $instanceId, $id): JsonResponse
    {
        /** @var RedmineProject $item */
        $item = DB::transaction(function () use ($instanceId, $id, $request): RedmineProject {
            $item = RedmineProject::where([
                'redmine_instance_id' => $instanceId,
                'id' => $id,
            ])->lockForUpdate()->firstOrFail();

            $data = $request->validated();
            $item->fill($data);
            if (array_key_exists('project_id', $data)) {
                $item->manual_unlinked = $data['project_id'] === null;
            }
            $item->save();

            return $item;
        });

        return response()->json($item);
    }
}
