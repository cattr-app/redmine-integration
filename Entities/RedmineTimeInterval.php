<?php

namespace Modules\RedmineIntegration\Entities;

use App\Models\TimeInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        return $this->belongsTo(TimeInterval::class, 'time_interval_id');
    }
}
