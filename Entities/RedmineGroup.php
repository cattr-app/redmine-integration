<?php

namespace Modules\RedmineIntegration\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modules\RedmineIntegration\Entities\RedmineGroup
 *
 * @property int $id
 * @property int $redmine_instance_id
 * @property int $redmine_group_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\RedmineIntegration\Entities\RedmineInstance $redmineInstance
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\RedmineIntegration\Entities\RedmineUser[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineGroup whereRedmineGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineGroup whereRedmineInstanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineGroup whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
     * @return BelongsToMany
     */
    /*public function usersRelation(): HasMany
    {
        return $this->hasMany(RedmineGroupUser::class, 'redmine_group_id', 'id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(RedmineUser::class, 'redmine_groups_users', 'redmine_group_id', 'redmine_user_id');
    }*/

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(RedmineUser::class, 'redmine_groups_users');
    }

    public function redmineInstance(): BelongsTo
    {
        return $this->belongsTo(RedmineInstance::class, 'redmine_instance_id');
    }
}
