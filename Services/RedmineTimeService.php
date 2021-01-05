<?php

namespace Modules\RedmineIntegration\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Entities\RedmineTask;
use Modules\RedmineIntegration\Entities\RedmineTimeInterval;
use Modules\RedmineIntegration\Entities\RedmineUser;
use Redmine\Client;
use SimpleXMLElement;

class RedmineTimeService
{
    public function synchronize(RedmineInstance $redmineInstance)
    {
        $relations = RedmineTimeInterval::with('timeInterval')->where([
            'redmine_instance_id' => $redmineInstance->id,
        ])->get();

        $groupedRelations = [];
        foreach ($relations as $relation) {
            // If related interval was soft-deleted
            if (!isset($relation->timeInterval)) {
                $relation->delete();
                continue;
            }

            $userId = $relation->timeInterval->user_id;
            $date = Carbon::parse($relation->timeInterval->start_at)->format('Y-m-d');

            $task = RedmineTask::where([
                'redmine_instance_id' => $redmineInstance->id,
                'task_id' => $relation->timeInterval->task_id,
            ])->first();
            if (!isset($task)) {
                $relation->delete();
                continue;
            }

            $issueId = $task->redmine_issue_id;
            $groupedRelations[$userId][$date][$issueId][] = $relation;
        }

        foreach ($groupedRelations as $userId => $userRelations) {
            $user = RedmineUser::where([
                'redmine_instance_id' => $redmineInstance->id,
                'user_id' => $userId,
            ])->first();

            if (!isset($user)) {
                continue;
            }

            $userClient = new Client($redmineInstance->url, $user->api_key);
            foreach ($userRelations as $date => $dateRelations) {
                $this->syncUserIntervals($userId, $userClient, $date, $dateRelations);
            }
        }
    }

    private function syncUserIntervals(int $userId, Client $client, string $date, array $relations)
    {
        foreach ($relations as $issueId => $taskRelations) {
            $duration = 0;
            foreach ($taskRelations as $relation) {
                $startAt = Carbon::parse($relation->timeInterval->start_at);
                $endAt = Carbon::parse($relation->timeInterval->end_at);
                $duration += (int)$endAt->floatDiffInSeconds($startAt);
            }

            try {
                /** @var SimpleXMLElement|false $result */
                $result = $client->time_entry->create([
                    'issue_id' => $issueId,
                    'spent_on' => $date,
                    'hours' => $duration / 3600.0,
                    'comments' => 'Uploaded from Cattr',
                ]);

                if (isset($result) && $result !== false) {
                    if (isset($result->error)) {
                        Log::error("Can't sync time to Redmine for user $userId:" . $result->error);
                    } else {
                        foreach ($taskRelations as $relation) {
                            $relation->delete();
                        }
                    }
                }
            } catch (Exception $e) {
                Log::error("Can't sync time to Redmine for user $userId:" . $e->getMessage());
            }
        }
    }
}
