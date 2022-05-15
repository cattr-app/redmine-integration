<?php

namespace Modules\RedmineIntegration\Subscribers;

use App\Models\Task;
use App\Models\TimeInterval;
use Modules\RedmineIntegration\Entities\RedminePriority;
use Modules\RedmineIntegration\Entities\RedmineProject;
use Modules\RedmineIntegration\Entities\RedmineTask;
use Modules\RedmineIntegration\Entities\RedmineTimeInterval;
use Modules\RedmineIntegration\Entities\RedmineUser;
use Modules\RedmineIntegration\Services\RedmineTaskService;

class EventObserver
{
    protected RedmineTaskService $redmineTaskService;

    public function __construct(RedmineTaskService $redmineTaskService)
    {
        $this->redmineTaskService = $redmineTaskService;
    }

    public function taskEditionAfter(Task $data): void
    {
        if ($redmineTask = RedmineTask::whereTaskId($data->id)->first()) {
            $redmineProject = RedmineProject::whereRedmineInstanceId($redmineTask->redmine_instance_id)
                ->where('project_id', '=', $data->project_id)
                ->firstOrFail();

            $redmineUsers = RedmineUser::whereRedmineInstanceId($redmineTask->redmine_instance_id)
                ->whereIn('user_id', $data->users->pluck('id'))
                ->pluck('redmine_user_id');

            $redminePriority = RedminePriority::whereRedmineInstanceId($redmineTask->redmine_instance_id)
                ->where('priority_id', '=', $data->priority_id)
                ->firstOrFail();

            $this->redmineTaskService->updateRedmineTask(
                $redmineTask,
                $redmineProject->redmine_project_id,
                $redmineUsers->all(),
                $redminePriority->redmine_priority_id,
                $data[0]
            );
        }
    }

    public function taskEdition(Task $data): void
    {
        abort_if(
            optional(optional(RedmineTask::whereTaskId($data->id)->first())->redmineInstance)->sync_type === 'cron',
            403,
            'Access denied to edit a task from Redmine integration'
        );
    }

    public function taskDeletion(mixed $taskId): void
    {
        abort_if(
            RedmineTask::whereTaskId($taskId)->exists(),
            403,
            'Access denied to delete a task from GitLab integration'
        );
    }

    public function intervalCreation(TimeInterval $data): void
    {
        $taskRelation = RedmineTask::whereTaskId($data->task_id)->first();
        if (isset($taskRelation, $taskRelation->redmineInstance)) {
            RedmineTimeInterval::create([
                'redmine_instance_id' => $taskRelation->redmineInstance->id,
                'time_interval_id' => $data->id,
            ]);
        }
    }

    public function subscribe(): array
    {
        return [
            'event.before.action.tasks.edit' => [[__CLASS__, 'taskEdition']],
            'event.after.action.tasks.edit' => [[__CLASS__, 'taskEditionAfter']],
            'event.before.action.task.destroy' => [[__CLASS__, 'taskDeletion']],
            'event.after.action.intervals.create' => [[__CLASS__, 'intervalCreation']]
        ];
    }
}
