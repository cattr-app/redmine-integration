<?php

namespace Modules\RedmineIntegration\Http\Controllers;

use App\Models\Priority;
use Illuminate\Http\JsonResponse;

class PriorityController extends AbstractRedmineController
{
    /**
     * @return array
     */
    public static function getControllerRules(): array
    {
        return [
            'index' => 'integration.redmine',
        ];
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $priorities = Priority::all();

        return new JsonResponse([
            'success' => true,
            'data' => $priorities,
        ]);
    }
}
