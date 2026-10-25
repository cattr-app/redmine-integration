<?php

namespace Modules\RedmineIntegration\Http\Controllers;

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

    public function webhook(Request $request, RedmineInstance $instance): Response
    {
        $json = $request->json()->all();
        $source = $request->attributes->get('redmine_webhook_source');

        if ($source === 'legacy') {
            // The historical Redmine plugin sends payload.issue and uses "assignee".
            $issue = $json['payload']['issue'] ?? null;
            if (is_array($issue) && !isset($issue['assigned_to']) && isset($issue['assignee'])) {
                $issue['assigned_to'] = $issue['assignee'];
            }
            $event = 'issue.updated';
        } else {
            // Native Redmine 7 sends a signed type + data.issue envelope.
            $event = $json['type'] ?? null;
            if (!is_string($event)) {
                return response('Invalid webhook payload', 422);
            }

            // Accept unrelated native events without importing them as issues.
            if (!in_array($event, ['issue.created', 'issue.updated', 'issue.deleted'], true)) {
                return response('', 204);
            }
            $issue = $json['data']['issue'] ?? null;
        }

        if (!is_array($issue) || !isset($issue['id']) || !filter_var($issue['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
            return response('Invalid issue payload', 422);
        }

        $issueId = (int) $issue['id'];
        if ($event === 'issue.deleted') {
            $this->redmineTaskService->deleteTaskFromWebhook($instance, $issueId);
        } else {
            foreach (['project', 'status', 'priority', 'subject'] as $field) {
                if (!isset($issue[$field])) {
                    return response('Invalid issue payload', 422);
                }
            }
            foreach (['project', 'status', 'priority'] as $field) {
                if (!is_array($issue[$field]) || !isset($issue[$field]['id'])) {
                    return response('Invalid issue payload', 422);
                }
            }
            $this->redmineTaskService->createTaskFromWebhook($instance, $issueId, $issue);
        }

        return response('', 204);
    }
}
