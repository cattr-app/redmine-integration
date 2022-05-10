<?php

namespace Modules\RedmineIntegration\Subscribers;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Pagination\Paginator;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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

    public function taskEditionAfter(array $data): void
    {
        if ($redmineTask = RedmineTask::whereTaskId($data[0]->id)->first()) {
            $redmineProject = RedmineProject::whereRedmineInstanceId($redmineTask->redmine_instance_id)
                ->where('project_id', '=', $data[0]->project_id)
                ->firstOrFail();

            $redmineUser = RedmineUser::whereRedmineInstanceId($redmineTask->redmine_instance_id)
                ->where('user_id', '=', $data[0]->user_id)
                ->firstOrFail();

            $redminePriority = RedminePriority::whereRedmineInstanceId($redmineTask->redmine_instance_id)
                ->where('priority_id', '=', $data[0]->priority_id)
                ->firstOrFail();

            $this->redmineTaskService->updateRedmineTask(
                $redmineTask,
                $redmineProject->redmine_project_id,
                $redmineUser->redmine_user_id,
                $redminePriority->redmine_priority_id,
                $data[0]
            );
        }
    }

    public function taskEdition(array $data): void
    {
        abort_if(
            optional(optional(RedmineTask::whereTaskId($data[0]->id)->first())->redmineInstance)->sync_type === 'cron',
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

    public function intervalCreation(array $data): void
    {
        $taskRelation = RedmineTask::whereTaskId($data[0]->task_id)->first();
        if (isset($taskRelation, $taskRelation->redmineInstance)) {
            RedmineTimeInterval::create([
                'redmine_instance_id' => $taskRelation->redmineInstance->id,
                'time_interval_id' => $data[0]->id,
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
