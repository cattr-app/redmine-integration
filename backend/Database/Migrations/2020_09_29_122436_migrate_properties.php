<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MigrateProperties extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $settings = DB::table('settings')
            ->where('module_name', 'redmine')
            ->select('value', 'key')
            ->pluck('value', 'key')
            ->toArray();
        if (!isset($settings['url']) || !isset($settings['api_key'])) {
            return;
        }

        $redmineInstanceId = DB::table('redmine_instances')->insertGetId([
            'url' => $settings['url'],
            'api_key' => $settings['api_key'],
            'sync_type' => 'cron',
            'sync_new_projects' => true,
            'project_sync_interval' => 5,
            'task_sync_interval' => 5,
            'priority_sync_interval' => 10,
            'status_sync_interval' => 10,
        ]);

        if (isset($settings['priorities'])) {
            $priorities = json_decode($settings['priorities']);
            foreach ($priorities as $priority) {
                DB::table('redmine_priorities')->insert([
                    'redmine_priority_id' => $priority->id,
                    'name' => $priority->name,
                    'redmine_instance_id' => $redmineInstanceId,
                    'priority_id' => $priority->priority_id,
                ]);
            }
        }

        if (isset($settings['statuses'])) {
            $statuses = json_decode($settings['statuses']);
            foreach ($statuses as $status) {
                DB::table('redmine_statuses')->insert([
                    'redmine_status_id' => $status->id,
                    'name' => $status->name,
                    'redmine_instance_id' => $redmineInstanceId,
                    'is_closed' => $status->is_closed,
                ]);
            }
        }

        $users = [];
        $userProperties = DB::table('properties')
            ->join('users', 'users.id', '=', 'properties.entity_id')
            ->where(['properties.entity_type' => 'user'])
            ->whereIn('properties.name', ['REDMINE_ID', 'REDMINE_KEY'])
            ->whereNull('properties.deleted_at')
            ->whereNull('users.deleted_at')
            ->select('properties.entity_id', 'properties.name', 'properties.value')
            ->get()
            ->toArray();
        foreach ($userProperties as $property) {
            $users[$property->entity_id][$property->name] = $property->value;
        }

        foreach ($users as $userId => $user) {
            if (isset($user['REDMINE_ID']) && isset($user['REDMINE_KEY'])) {
                DB::table('redmine_users')->insert([
                    'redmine_user_id' => $user['REDMINE_ID'],
                    'redmine_instance_id' => $redmineInstanceId,
                    'api_key' => $user['REDMINE_KEY'],
                    'user_id' => $userId,
                ]);
            }
        }

        $projects = DB::table('properties')
            ->join('projects', 'projects.id', '=', 'properties.entity_id')
            ->where([
                'properties.entity_type' => 'project',
                'properties.name' => 'REDMINE_ID',
            ])
            ->whereNull('properties.deleted_at')
            ->whereNull('projects.deleted_at')
            ->select('properties.entity_id', 'properties.value', 'projects.name')
            ->get()
            ->toArray();

        foreach ($projects as $project) {
            DB::table('redmine_projects')->insert([
                'redmine_project_id' => $project->value,
                'name' => $project->name,
                'redmine_instance_id' => $redmineInstanceId,
                'project_id' => $project->entity_id,
            ]);
        }

        $tasks = DB::table('properties')
            ->join('tasks', 'tasks.id', '=', 'properties.entity_id')
            ->where([
                'properties.entity_type' => 'task',
                'properties.name' => 'REDMINE_ID',
            ])
            ->whereNull('properties.deleted_at')
            ->whereNull('tasks.deleted_at')
            ->select('properties.entity_id', 'properties.value')
            ->get()
            ->toArray();

        foreach ($tasks as $task) {
            DB::table('redmine_tasks')->insert([
                'redmine_issue_id' => $task->value,
                'redmine_instance_id' => $redmineInstanceId,
                'task_id' => $task->entity_id,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
