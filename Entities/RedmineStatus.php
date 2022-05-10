<?php

namespace Modules\RedmineIntegration\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modules\RedmineIntegration\Entities\RedmineStatus
 *
 * @property int $id
 * @property int $redmine_status_id
 * @property string $name
 * @property int $redmine_instance_id
 * @property int $is_closed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\RedmineIntegration\Entities\RedmineInstance $redmineInstance
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineStatus whereIsClosed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineStatus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineStatus whereRedmineInstanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineStatus whereRedmineStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineStatus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RedmineStatus extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'redmine_status_id',
        'name',
        'redmine_instance_id',
        'is_closed',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'redmine_status_id' => 'int',
        'redmine_instance_id' => 'int',
        'is_closed' => 'int',
    ];

    public function redmineInstance(): BelongsTo
    {
        return $this->belongsTo(RedmineInstance::class, 'redmine_instance_id');
    }
}
