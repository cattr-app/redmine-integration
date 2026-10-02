<?php

namespace Modules\RedmineIntegration\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Services\RedmineTaskService;

class RedmineWebhookController extends BaseController
{
    public function __construct(protected RedmineTaskService $redmineTaskService)
    {
    }

    public function webhook(Request $request, RedmineInstance $instance): Response|Application|ResponseFactory
    {
        $json = json_decode($request->getContent(), true);
        $data = $json['payload'];
        $this->redmineTaskService->createTaskFromWebhook($instance, $data['issue']['id'], $data['issue']);

        return response('', 204);
    }
}
