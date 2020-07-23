<?php

namespace Modules\RedmineIntegration\Entities;

use Illuminate\Database\Eloquent\Model;

class RedmineInstance extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'url',
        'api_key',
        'sync_type',
        'sync_new_projects',
        'project_sync_interval',
        'task_sync_interval',
        'priority_sync_interval',
        'status_sync_interval',
    ];

    /**
     * @var array
     */
    protected $hidden = [
        'api_key',
    ];
}
