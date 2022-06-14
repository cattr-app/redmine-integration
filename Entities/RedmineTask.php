<?php

namespace Modules\RedmineIntegration\Entities;

use App\Models\Task;
use App\Scopes\TaskAccessScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modules\RedmineIntegration\Entities\RedmineTask
 *
 * @property int $id
 * @property int $redmine_issue_id
 * @property int $redmine_instance_id
 * @property int|null $task_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\RedmineIntegration\Entities\RedmineInstance $redmineInstance
 * @property-read Task|null $task
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineTask newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineTask newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineTask query()
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineTask whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineTask whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineTask whereRedmineInstanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineTask whereRedmineIssueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineTask whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineTask whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RedmineTask extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'redmine_issue_id',
        'redmine_instance_id',
        'task_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'redmine_issue_id' => 'int',
        'redmine_instance_id' => 'int',
        'task_id' => 'int',
    ];

    public function redmineInstance(): BelongsTo
    {
        return $this->belongsTo(RedmineInstance::class, 'redmine_instance_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id')->withoutGlobalScope(TaskAccessScope::class);
    }
}
