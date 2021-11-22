<?php

namespace Modules\RedmineIntegration\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Services\RedmineTaskService;

class RedmineWebhookController extends BaseController
{
    protected RedmineTaskService $redmineTaskService;

    public function __construct(RedmineTaskService $redmineTaskService)
    {
        $this->redmineTaskService = $redmineTaskService;
    }

    public function webhook(Request $request, RedmineInstance $instance)
    {
        $json = json_decode($request->getContent(), true);
        $data = $json['payload'];
        $this->redmineTaskService->createTaskFromWebhook($instance, $data['issue']['id'], $data['issue']);

        return response('', 204);
    }
}
