<?php

namespace Modules\RedmineIntegration\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RedmineGroup extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'redmine_group_id',
        'redmine_instance_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'redmine_group_id' => 'int',
        'redmine_instance_id' => 'int',
    ];

    /**
     * @return HasMany
     */
    /*public function usersRelation(): HasMany
    {
        return $this->hasMany(RedmineGroupUser::class, 'redmine_group_id', 'id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(RedmineUser::class, 'redmine_groups_users', 'redmine_group_id', 'redmine_user_id');
    }*/

    public function users()
    {
        return $this->belongsToMany(RedmineUser::class, 'redmine_groups_users');
    }

    public function redmineInstance(): BelongsTo
    {
        return $this->belongsTo(RedmineInstance::class, 'redmine_instance_id');
    }
}
