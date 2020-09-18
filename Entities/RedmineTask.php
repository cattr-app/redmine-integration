<?php

namespace Modules\RedmineIntegration\Entities;

use App\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        return $this->belongsTo(Task::class, 'task_id');
    }
}
