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

    protected function getOrigin(string $url): string
    {
        $parsedUrl = parse_url($url);
        $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $host = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
        $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
        return $scheme . $host . $port;
    }

    public function webhook(Request $request)
    {
        $data = $request->json()['parameters']['payload'];
        $origin = $this->getOrigin($data['url']);
        $instance = RedmineInstance::where(['url' => $origin])->firstOrFail();
        $this->redmineTaskService->createTaskFromWebhook($instance, $data['issue']['id'], $data['issue']);

        return response('', 204);
    }
}
