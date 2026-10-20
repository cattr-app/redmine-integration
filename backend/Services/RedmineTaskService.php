<?php

namespace Modules\RedmineIntegration\Services;

use App\Models\Project;
use App\Models\Task;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\RedmineIntegration\Entities\RedmineGroup;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Entities\RedminePriority;
use Modules\RedmineIntegration\Entities\RedmineProject;
use Modules\RedmineIntegration\Entities\RedmineStatus;
use Modules\RedmineIntegration\Entities\RedmineTask;
use Modules\RedmineIntegration\Entities\RedmineUser;
use Redmine\Client\Client;

class RedmineTaskService
{
    public function __construct(protected RedmineGroupService $groupService)
    {
    }

    protected function createRedmineTask(RedmineInstance $redmineInstance, int $redmineIssueId, array $data): void
    {
        $existingRedmineTask = RedmineTask::where([
            'redmine_instance_id' => $redmineInstance->id,
            'redmine_issue_id' => $redmineIssueId,
        ])->first();

        $redmineProject = RedmineProject::where([
            'redmine_instance_id' => $redmineInstance->id,
            'redmine_project_id' => $data['project']['id'],
        ])->first();

        // An external project may intentionally have no Cattr counterpart.
        // Deleted Cattr projects are also hidden by the relation's SoftDeletes scope.
        if (!$redmineProject || !$redmineProject->project) {
            return;
        }

        $redmineStatus = RedmineStatus::where([
            'redmine_instance_id' => $redmineInstance->id,
            'redmine_status_id' => $data['status']['id'],
        ])->first();

        $redminePriority = RedminePriority::where([
            'redmine_instance_id' => $redmineInstance->id,
            'redmine_priority_id' => $data['priority']['id'],
        ])->first();

        if (isset($data['assigned_to'])) {
            $redmineGroup = RedmineGroup::where([
                'redmine_instance_id' => $redmineInstance->id,
                'redmine_group_id' => $data['assigned_to']['id'],
            ])->first();

            if (isset($redmineGroup)) {
                $redmineUsers = $redmineGroup->users;
            } else {
                $redmineUser = RedmineUser::where([
                    'redmine_instance_id' => $redmineInstance->id,
                    'redmine_user_id' => $data['assigned_to']['id'],
                ])->first();

                if (isset($redmineUser)) {
                    $redmineUsers = [$redmineUser];
                } else {
                    $redmineUsers = [];
                }
            }
        } else {
            $redmineUsers = [];
        }

        $userIds = collect($redmineUsers)->pluck('user_id');

        $redmineProject->project->users()->syncWithoutDetaching($userIds);

        $url = $redmineInstance->url;
        if (substr($url, -1) !== '/') {
            $url .= '/';
        }

        $url .= 'issues/' . $redmineIssueId;

        if (isset($existingRedmineTask)) {
            if (isset($existingRedmineTask->task)) {
                $existingRedmineTask->task->fill([
                    'task_name' => $data['subject'],
                    'description' => $data['description'],
                    'project_id' => $redmineProject->project_id,
                    'priority_id' => $redminePriority->priority_id,
                    'status_id' => $redmineStatus->status_id,
                    'url' => $url,
                ]);

                $existingRedmineTask->task->users()->sync($userIds);
                $existingRedmineTask->task->save();
            } else {
                $task = Task::create([
                    'task_name' => $data['subject'],
                    'description' => $data['description'],
                    'project_id' => $redmineProject->project_id,
                    'priority_id' => $redminePriority->priority_id,
                    'status_id' => $redmineStatus->status_id,
                    'url' => $url,
                    'important' => false,
                ]);

                $task->users()->sync($userIds);
                $existingRedmineTask->task_id = $task->id;
                $existingRedmineTask->save();
            }
        } else {
            $task = Task::create([
                'task_name' => $data['subject'],
                'description' => $data['description'],
                'project_id' => $redmineProject->project_id,
                'priority_id' => $redminePriority->priority_id,
                'status_id' => $redmineStatus->status_id,
                'url' => $url,
                'important' => false,
            ]);

            $task->users()->sync($userIds);

            RedmineTask::create([
                'redmine_issue_id' => $redmineIssueId,
                'redmine_instance_id' => $redmineInstance->id,
                'task_id' => $task->id,
            ]);
        }
    }

    public function synchronize(RedmineInstance $redmineInstance): void
    {
        $client = app(Client::class, ['url' => $redmineInstance->url, 'apikeyOrUsername' => $redmineInstance->api_key]);

        foreach (RedminePaginator::items(
            static fn (int $offset, int $limit) => $client->getApi('issue')->all([
                'status_id' => '*',
                'offset' => $offset,
                'limit' => $limit,
            ]),
            'issues'
        ) as $issue) {
            $this->createRedmineTask($redmineInstance, $issue['id'], Arr::only($issue, [
                'subject',
                'description',
                'project',
                'status',
                'priority',
                'assigned_to',
            ]));
        }
    }

    public function createTaskFromWebhook(RedmineInstance $redmineInstance, int $redmineIssueId, array $data): void
    {
        $existingRedmineTask = RedmineTask::where([
            'redmine_instance_id' => $redmineInstance->id,
            'redmine_issue_id' => $redmineIssueId,
        ])->first();

        $redmineProject = RedmineProject::where([
            'redmine_instance_id' => $redmineInstance->id,
            'redmine_project_id' => $data['project']['id'],
        ])->first();

        $redmineStatus = RedmineStatus::where([
            'redmine_instance_id' => $redmineInstance->id,
            'redmine_status_id' => $data['status']['id'],
        ])->first();

        $redminePriority = RedminePriority::where([
            'redmine_instance_id' => $redmineInstance->id,
            'redmine_priority_id' => $data['priority']['id'],
        ])->first();

        if (isset($data['assignee'])) {
            $redmineGroup = RedmineGroup::where([
                'redmine_instance_id' => $redmineInstance->id,
                'redmine_group_id' => $data['assignee']['id'],
            ])->first();

            if (isset($redmineGroup)) {
                $redmineUsers = $redmineGroup->users;
            } else {
                $redmineUser = RedmineUser::where([
                    'redmine_instance_id' => $redmineInstance->id,
                    'redmine_user_id' => $data['assignee']['id'],
                ])->first();

                if (isset($redmineUser)) {
                    $redmineUsers = [$redmineUser];
                } else {
                    $redmineUsers = [];
                }
            }
        } else {
            $redmineUsers = [];
        }

        $userIds = collect($redmineUsers)->pluck('user_id');

        // Serialize project discovery and linking for concurrent webhooks and cron.
        // Newly registered mappings are eligible; manually cleared ones are not.
        $redmineProject = DB::transaction(function () use ($redmineInstance, $data): RedmineProject {
            RedmineInstance::whereKey($redmineInstance->id)->lockForUpdate()->firstOrFail();

            $mapping = RedmineProject::where([
                'redmine_instance_id' => $redmineInstance->id,
                'redmine_project_id' => $data['project']['id'],
            ])->lockForUpdate()->first();

            if (!$mapping) {
                $mapping = new RedmineProject([
                    'redmine_project_id' => $data['project']['id'],
                    'redmine_instance_id' => $redmineInstance->id,
                    'manual_unlinked' => false,
                ]);
            }

            $mapping->name = $data['project']['name'];
            if (RedmineProjectLinkPolicy::shouldAutoLink(
                $mapping->project_id,
                $mapping->manual_unlinked,
                $redmineInstance->sync_type,
                (bool) $redmineInstance->sync_new_projects
            )) {
                $project = Project::create([
                    'name' => $data['project']['name'],
                    'description' => $data['project']['description'] ?? $data['project']['name'],
                    'source' => 'redmine',
                ]);
                $mapping->project_id = $project->id;
            }

            $mapping->save();

            return $mapping;
        });

        // Do not recreate a deliberately deleted Cattr project.
        if (!$redmineProject->project) {
            return;
        }

        $redmineProject->project->users()->syncWithoutDetaching($userIds);

        $url = $redmineInstance->url;
        if (substr($url, -1) !== '/') {
            $url .= '/';
        }

        $url .= 'issues/' . $redmineIssueId;

        if (isset($existingRedmineTask)) {
            if (isset($existingRedmineTask->task)) {
                $existingRedmineTask->task->fill([
                    'task_name' => $data['subject'],
                    'description' => $data['description'],
                    'project_id' => $redmineProject->project_id,
                    'priority_id' => $redminePriority->priority_id,
                    'status_id' => $redmineStatus->status_id,
                    'url' => $url,
                ]);

                $existingRedmineTask->task->users()->sync($userIds);
                $existingRedmineTask->task->save();
            } else {
                $task = Task::create([
                    'task_name' => $data['subject'],
                    'description' => $data['description'],
                    'project_id' => $redmineProject->project_id,
                    'priority_id' => $redminePriority->priority_id,
                    'status_id' => $redmineStatus->status_id,
                    'url' => $url,
                    'important' => false,
                ]);

                $task->users()->sync($userIds);
                $existingRedmineTask->task_id = $task->id;
                $existingRedmineTask->save();
            }
        } else {
            $task = Task::create([
                'task_name' => $data['subject'],
                'description' => $data['description'],
                'project_id' => $redmineProject->project_id,
                'priority_id' => $redminePriority->priority_id,
                'status_id' => $redmineStatus->status_id,
                'url' => $url,
                'important' => false,
            ]);
            $task->users()->sync($userIds);

            RedmineTask::create([
                'redmine_issue_id' => $redmineIssueId,
                'redmine_instance_id' => $redmineInstance->id,
                'task_id' => $task->id,
            ]);
        }
    }

    public function updateRedmineTask(
        RedmineTask $redmineTask,
        int         $redmineProjectId,
        array       $redmineUsers,
        int         $redminePriorityId,
        Task        $task
    ): void
    {
        $redmineInstance = $redmineTask->redmineInstance;
        $client = app(Client::class, ['url' => $redmineInstance->url, 'apikeyOrUsername' => $redmineInstance->api_key]);
        $client->getApi('issue')->update($redmineTask->redmine_issue_id, [
            'project_id' => $redmineProjectId,
            'subject' => $task->task_name,
            'description' => $task->description,
            'assigned_to_id' => $redmineUsers,
            'priority_id' => $redminePriorityId,
        ]);
    }
}
