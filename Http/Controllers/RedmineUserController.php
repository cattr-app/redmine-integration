<?php

namespace Modules\RedmineIntegration\Http\Controllers;

use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Http\Controllers\AbstractRedmineController;
use Modules\RedmineIntegration\Http\Requests\CreateRedmineUser;
use Modules\RedmineIntegration\Services\RedmineUserService;

class RedmineUserController extends AbstractRedmineController
{
    protected RedmineUserService $redmineUserService;

    public function __construct(RedmineUserService $redmineUserService)
    {
        parent::__construct();

        $this->redmineUserService = $redmineUserService;
    }

    public function create(CreateRedmineUser $request, $instanceId)
    {
        $instance = RedmineInstance::findOrFail($instanceId);
        $item = $this->redmineUserService->create($instance, $request->validated());

        return response()->json($item);
    }

    public function checkAuth($instanceId)
    {
        $user = auth()->user();
        $instance = RedmineInstance::findOrFail($instanceId);
        $result = $this->redmineUserService->checkAuth($instance, $user);

        return response()->json(['status' => $result]);
    }
}
