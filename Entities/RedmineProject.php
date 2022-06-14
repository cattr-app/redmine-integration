<?php

namespace Modules\RedmineIntegration\Entities;

use App\Models\Project;
use App\Scopes\ProjectAccessScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modules\RedmineIntegration\Entities\RedmineProject
 *
 * @property int $id
 * @property int $redmine_project_id
 * @property string $name
 * @property int $redmine_instance_id
 * @property int|null $project_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Project|null $project
 * @property-read \Modules\RedmineIntegration\Entities\RedmineInstance $redmineInstance
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineProject newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineProject newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineProject query()
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineProject whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineProject whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineProject whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineProject whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineProject whereRedmineInstanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineProject whereRedmineProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineProject whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
        return $this->belongsTo(Project::class, 'project_id')->withoutGlobalScope(ProjectAccessScope::class);
    }
}
