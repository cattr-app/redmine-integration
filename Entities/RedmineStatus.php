<?php

namespace Modules\RedmineIntegration\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
