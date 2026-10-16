<?php

namespace Modules\RedmineIntegration\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Modules\RedmineIntegration\Entities\RedmineInstance
 *
 * @property int $id
 * @property string $url
 * @property string $api_key
 * @property string $sync_type
 * @property int $sync_new_projects
 * @property int $project_sync_interval
 * @property int $task_sync_interval
 * @property int $priority_sync_interval
 * @property int $status_sync_interval
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineInstance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineInstance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineInstance query()
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineInstance whereApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineInstance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineInstance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineInstance wherePrioritySyncInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineInstance whereProjectSyncInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineInstance whereStatusSyncInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineInstance whereSyncNewProjects($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineInstance whereSyncType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineInstance whereTaskSyncInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineInstance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineInstance whereUrl($value)
 * @mixin \Eloquent
 */
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
        'webhook_secret',
    ];

    /**
     * @var array
     */
    protected $hidden = [
        'api_key',
        'webhook_secret',
    ];

    protected $appends = ['webhook_secret_configured'];

    /**
     * @var array
     */
    protected $casts = [
        'sync_new_projects' => 'int',
        'project_sync_interval' => 'int',
        'task_sync_interval' => 'int',
        'priority_sync_interval' => 'int',
        'status_sync_interval' => 'int',
        'webhook_secret' => 'encrypted',
    ];
    public function getWebhookSecretConfiguredAttribute(): bool
    {
        return !empty($this->webhook_secret);
    }
}
