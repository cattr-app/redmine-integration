<?php

namespace Modules\RedmineIntegration\Entities;

use App\Models\TimeInterval;
use App\Scopes\TimeIntervalAccessScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modules\RedmineIntegration\Entities\RedmineTimeInterval
 *
 * @property int $id
 * @property int $redmine_instance_id
 * @property int $time_interval_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\RedmineIntegration\Entities\RedmineInstance $redmineInstance
 * @property-read TimeInterval $timeInterval
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineTimeInterval newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineTimeInterval newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineTimeInterval query()
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineTimeInterval whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineTimeInterval whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineTimeInterval whereRedmineInstanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineTimeInterval whereTimeIntervalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineTimeInterval whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RedmineTimeInterval extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'redmine_instance_id',
        'time_interval_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'redmine_instance_id' => 'int',
        'time_interval_id' => 'int',
    ];

    public function redmineInstance(): BelongsTo
    {
        return $this->belongsTo(RedmineInstance::class, 'redmine_instance_id');
    }

    public function timeInterval(): BelongsTo
    {
        return $this->belongsTo(TimeInterval::class, 'time_interval_id')->withoutGlobalScope(TimeIntervalAccessScope::class);
    }
}
