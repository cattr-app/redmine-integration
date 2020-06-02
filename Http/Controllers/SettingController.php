<?php

namespace Modules\RedmineIntegration\Http\Controllers;

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
        $this->settings = $settings;
    }

    /**
     * Get all settings.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $settings = $this->settings->all();

        return new JsonResponse([
            'success' => true,
            'data' => $settings,
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
            'success' => true,
            'data' => $settings,
        ]);
    }
}
