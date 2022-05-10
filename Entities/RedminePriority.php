<?php

namespace Modules\RedmineIntegration\Entities;

use App\Models\Priority;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modules\RedmineIntegration\Entities\RedminePriority
 *
 * @property int $id
 * @property int $redmine_priority_id
 * @property string $name
 * @property int $redmine_instance_id
 * @property int $priority_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Priority $priority
 * @property-read \Modules\RedmineIntegration\Entities\RedmineInstance $redmineInstance
 * @method static \Illuminate\Database\Eloquent\Builder|RedminePriority newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RedminePriority newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RedminePriority query()
 * @method static \Illuminate\Database\Eloquent\Builder|RedminePriority whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedminePriority whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedminePriority whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedminePriority wherePriorityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedminePriority whereRedmineInstanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedminePriority whereRedminePriorityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedminePriority whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RedminePriority extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'redmine_priority_id',
        'name',
        'redmine_instance_id',
        'priority_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'redmine_priority_id' => 'int',
        'redmine_instance_id' => 'int',
        'priority_id' => 'int',
    ];

    public function redmineInstance(): BelongsTo
    {
        return $this->belongsTo(RedmineInstance::class, 'redmine_instance_id');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class, 'priority_id');
    }
}
