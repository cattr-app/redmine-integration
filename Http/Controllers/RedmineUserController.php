<?php

namespace Modules\RedmineIntegration\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Http\Controllers\AbstractRedmineController;
use Modules\RedmineIntegration\Http\Requests\CreateRedmineUser;
use Modules\RedmineIntegration\Services\RedmineUserService;

class RedmineUserController extends AbstractRedmineController
{
    public function __construct(protected RedmineUserService $redmineUserService)
    {
    }

    public static function getControllerRules(): array
    {
        return [
            'create' => 'integration.redmine',
            'checkAuth' => 'integration.redmine',
        ];
    }

    public function create(CreateRedmineUser $request, $instanceId): JsonResponse
    {
        $instance = RedmineInstance::findOrFail($instanceId);
        $item = $this->redmineUserService->create($instance, $request->validated());

        return response()->json($item);
    }

    public function checkAuth($instanceId): JsonResponse
    {
        $user = auth()->user();
        $instance = RedmineInstance::findOrFail($instanceId);
        $result = $this->redmineUserService->checkAuth($instance, $user);

        return response()->json(['status' => $result]);
    }
}
