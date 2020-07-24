<?php

namespace Modules\RedmineIntegration\Entities;

use App\Models\Priority;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function redmineInstance(): BelongsTo
    {
        return $this->belongsTo(RedmineInstance::class, 'redmine_instance_id');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class, 'priority_id');
    }
}
