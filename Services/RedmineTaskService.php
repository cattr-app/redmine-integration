<?php

namespace Modules\RedmineIntegration\Services;

use App\Models\Project;
use App\Models\Task;
use Exception;
use Illuminate\Support\Arr;
use Modules\RedmineIntegration\Entities\RedmineGroup;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Entities\RedminePriority;
use Modules\RedmineIntegration\Entities\RedmineProject;
use Modules\RedmineIntegration\Entities\RedmineStatus;
use Modules\RedmineIntegration\Entities\RedmineTask;
use Modules\RedmineIntegration\Entities\RedmineUser;
use Redmine\Client;

class RedmineTaskService
{
    protected RedmineGroupService $groupService;

    public function __construct(RedmineGroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    protected function createRedmineTask(RedmineInstance $redmineInstance, int $redmineIssueId, array $data)
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

        $active = !$redmineStatus->is_closed;

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
                    'active' => $active,
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
                    'active' => $active,
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
                'active' => $active,
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

    public function synchronize(RedmineInstance $redmineInstance)
    {
        $client = new Client($redmineInstance->url, $redmineInstance->api_key);
        $response = $client->issue->all();
        if (!is_array($response) || !isset($response['issues'])) {
            throw new Exception('Invalid credentials or Redmine not available. Please contact your administrator');
        }

        foreach ($response['issues'] as $issue) {
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

    public function createTaskFromWebhook(RedmineInstance $redmineInstance, int $redmineIssueId, array $data)
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

        if (!isset($redmineProject)) {
            if ($redmineInstance->sync_new_projects) {
                $project = Project::create([
                    'name' => $data['project']['name'],
                    'description' => $data['project']['description'],
                    'source' => 'redmine',
                ]);
            } else {
                $project = null;
            }

            $redmineProject = RedmineProject::create([
                'redmine_project_id' => $data['project']['id'],
                'name' => $data['project']['name'],
                'redmine_instance_id' => $redmineInstance->id,
                'project_id' => isset($project) ? $project->id : null,
            ]);
        }

        if (!isset($redmineProject->project_id)) {
            return;
        }

        $active = !$redmineStatus->is_closed;

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
                    'active' => $active,
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
                    'active' => $active,
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
                'active' => $active,
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
        int $redmineProjectId,
        int $redmineUserId,
        int $redminePriorityId,
        Task $task
    ) {
        $redmineInstance = $redmineTask->redmineInstance;
        $client = new Client($redmineInstance->url, $redmineInstance->api_key);
        $client->issue->update($redmineTask->redmine_issue_id, [
            'project_id' => $redmineProjectId,
            'subject' => $task->task_name,
            'description' => $task->description,
            'assigned_to_id' => $redmineUserId,
            'priority_id' => $redminePriorityId,
        ]);
    }
}
