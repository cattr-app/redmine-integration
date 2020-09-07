<?php

namespace Modules\RedmineIntegration\Http\Controllers;

use Modules\RedmineIntegration\Entities\RedmineProject;
use Modules\RedmineIntegration\Http\Requests\UpdateRedmineProject;

class RedmineProjectController extends AbstractRedmineController
{
    public function list($instanceId)
    {
        $items = RedmineProject::where(['redmine_instance_id' => $instanceId])->get();

        return response()->json($items);
    }

    public function show($instanceId, $id)
    {
        /** @var RedmineProject $item */
        $item = RedmineProject::where([
            'redmine_instance_id' => $instanceId,
            'id'                  => $id,
        ]);

        return response()->json($item);
    }

    public function update(UpdateRedmineProject $request, $instanceId, $id)
    {
        /** @var RedmineProject $item */
        $item = RedmineProject::where([
            'redmine_instance_id' => $instanceId,
            'id'                  => $id,
        ])->firstOrFail();

        $item->fill($request->validated());
        $item->save();

        return response()->json($item);
    }
}
