<?php

namespace Modules\RedmineIntegration\Entities;

use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RedmineProject extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'redmine_project_id',
        'name',
        'redmine_instance_id',
        'project_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'redmine_project_id' => 'int',
        'redmine_instance_id' => 'int',
        'project_id' => 'int',
    ];

    public function redmineInstance(): BelongsTo
    {
        return $this->belongsTo(RedmineInstance::class, 'redmine_instance_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
