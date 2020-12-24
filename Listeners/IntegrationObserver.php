<?php

namespace Modules\RedmineIntegration\Listeners;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Pagination\Paginator;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Modules\RedmineIntegration\Entities\RedminePriority;
use Modules\RedmineIntegration\Entities\RedmineProject;
use Modules\RedmineIntegration\Entities\RedmineTask;
use Modules\RedmineIntegration\Entities\RedmineTimeInterval;
use Modules\RedmineIntegration\Entities\RedmineUser;
use Modules\RedmineIntegration\Services\RedmineTaskService;

class IntegrationObserver
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    protected RedmineTaskService $redmineTaskService;

    public function __construct(RedmineTaskService $redmineTaskService)
    {
        $this->redmineTaskService = $redmineTaskService;
    }

    /**
     * Observe task creation
     *
     * If task's project is Redmine project => mark task as NEW for synchronization
     *
     * @param $task
     *
     * @return mixed
     */
    public function taskCreation($task)
    {
        return $task;
    }

    /**
     * Observe user after edition
     *
     * @param $json
     *
     * @return mixed
     */
    public function userAfterEdition($json)
    {
        return $json;
    }

    /**
     * Observe user show
     *
     * @param $user
     *
     * @return mixed
     */
    public function userShow($user)
    {
        return $user;
    }

    /**
     * Observe task edition
     *
     * @param $task
     *
     * @return mixed
     */
    public function taskEdition($task)
    {
        $redmineTask = RedmineTask::where(['task_id' => $task->id])->first();
        if (isset($redmineTask)) {
            $redmineInstance = $redmineTask->redmineInstance;
            if ($redmineInstance->sync_type === 'cron') {
                abort(403, 'Access denied to edit a task from Redmine integration');
            } else {
                $redmineProject = RedmineProject::where([
                    'redmine_instance_id' => $redmineTask->redmine_instance_id,
                    'project_id' => $task->project_id,
                ])->first();
                if (!isset($redmineProject)) {
                    abort(400, 'Project not found in the Redmine');
                }

                $redmineUser = RedmineUser::where([
                    'redmine_instance_id' => $redmineTask->redmine_instance_id,
                    'user_id' => $task->user_id,
                ])->first();
                if (!isset($redmineUser)) {
                    abort(400, 'User not found in the Redmine');
                }

                $redminePriority = RedminePriority::where([
                    'redmine_instance_id' => $redmineTask->redmine_instance_id,
                    'priority_id' => $task->priority_id,
                ])->first();
                if (!isset($redminePriority)) {
                    abort(400, 'Priority not found in the Redmine');
                }

                $this->redmineTaskService->updateRedmineTask(
                    $redmineTask,
                    $redmineProject->redmine_project_id,
                    $redmineUser->redmine_user_id,
                    $redminePriority->redmine_priority_id,
                    $task
                );
            }
        }

        return $task;
    }

    /**
     * Observe task deletion
     *
     * @param $task
     *
     * @return mixed
     */
    public function taskDeletion($task)
    {
        if (RedmineTask::where(['task_id' => $task->id])->first() !== null) {
            abort(403, 'Access denied to delete a task from Redmine integration');
        }

        return $task;
    }

    /**
     * Observe task list
     *
     * @param Collection|Paginator $tasks
     * @return Paginator|Collection
     */
    public function taskList($tasks)
    {
        if ($tasks instanceof Paginator) {
            $items = $tasks->getCollection();
        } else {
            $items = $tasks;
        }

        $taskIds = $items->map(static function ($task) {
            return $task->id;
        })->toArray();
        $redmineTaskIds = RedmineTask::whereHas('redmineInstance', static function ($query) {
            $query->where('sync_type', 'cron');
        })->whereIn('task_id', $taskIds)->pluck('task_id')->toArray();

        $items->transform(static function ($item) use ($redmineTaskIds) {
            if (in_array($item->id, $redmineTaskIds)) {
                $item->integration = 'redmine';
            }

            return $item;
        });

        if ($tasks instanceof Paginator) {
            $tasks->setCollection($items);
        } else {
            $tasks = $items;
        }

        return $tasks;
    }

    public function rulesHook($rules)
    {
        if (!isset($rules['integration'])) {
            $rules['integration'] = [];
        }

        $rules['integration']['redmine'] = __('Redmine integration');

        return $rules;
    }

    public function timeIntervalCreation($response)
    {
        $taskRelation = RedmineTask::where([
            'task_id' => $response['interval']->task_id,
        ])->first();
        if (isset($taskRelation) && isset($taskRelation->redmineInstance)) {
            RedmineTimeInterval::create([
                'redmine_instance_id' => $taskRelation->redmineInstance->id,
                'time_interval_id' => $response['interval']->id,
            ]);
        }

        return $response;
    }
}
