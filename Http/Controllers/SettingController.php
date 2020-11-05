<?php

namespace Modules\RedmineIntegration\Http\Controllers;

use App\Models\Priority;
use Illuminate\Http\JsonResponse;
use Modules\RedmineIntegration\Http\Requests\Setting\UpdateSettingsRequest;
use Modules\RedmineIntegration\Services\SettingsService;

class SettingController extends AbstractRedmineController
{
    /**
     * @var SettingsService
     */
    protected SettingsService $settings;

    /**
     * SettingController constructor.
     * @param SettingsService $settings
     */
    public function __construct(SettingsService $settings)
    {
        parent::__construct();

        $this->settings = $settings;
    }

    /**
     * @return array
     */
    public static function getControllerRules(): array
    {
        return [
            'index' => 'integration.redmine-settings',
            'update' => 'integration.redmine-settings',
        ];
    }

    /**
     * Get all settings.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $settings = $this->settings->all();
        $priorities = Priority::all();

        $data = $settings;
        $data['internal_priorities'] = $priorities;

        return new JsonResponse([
            'data' => $data,
        ]);
    }

    /**
     * Update the settings.
     *
     * @param UpdateSettingsRequest $request
     * @return JsonResponse
     */
    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $settings = $this->settings->set($request->validated());

        return new JsonResponse([
            'data' => $settings,
        ]);
    }
}
