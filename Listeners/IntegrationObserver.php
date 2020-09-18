<?php

namespace Modules\RedmineIntegration\Listeners;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Pagination\Paginator;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Modules\RedmineIntegration\Entities\RedmineTask;

class IntegrationObserver
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

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
        if (RedmineTask::where(['task_id' => $task->id])->first() !== null) {
            abort(403, 'Access denied to edit a task from Redmine integration');
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
        $redmineTaskIds = RedmineTask::whereIn('task_id', $taskIds)->pluck('task_id')->toArray();

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
}
