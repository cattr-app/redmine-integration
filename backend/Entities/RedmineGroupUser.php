<?php

namespace Modules\RedmineIntegration\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modules\RedmineIntegration\Entities\RedmineGroupUser
 *
 * @property int $redmine_group_id
 * @property int $redmine_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|RedmineGroupUser newModelQuery()
 * @method static Builder|RedmineGroupUser newQuery()
 * @method static Builder|RedmineGroupUser query()
 * @method static Builder|RedmineGroupUser whereCreatedAt($value)
 * @method static Builder|RedmineGroupUser whereRedmineGroupId($value)
 * @method static Builder|RedmineGroupUser whereRedmineUserId($value)
 * @method static Builder|RedmineGroupUser whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RedmineGroupUser extends Model
{
    /**
     * table name from database
     *
     * @var string
     */
    protected $table = 'redmine_groups_users';

    /**
     * @var array
     */
    protected $fillable = [
        'redmine_group_id',
        'user_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'redmine_group_id' => 'integer',
        'redmine_user_id' => 'integer',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'created_at',
        'redmine_user_id',
    ];

    /*public function group(): BelongsTo
    {
        return $this->belongsTo(RedmineGroup::class, 'redmine_group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(RedmineUser::class, 'redmine_user_id');
    }*/

    protected function setKeysForSaveQuery($query): Builder
    {
        return $query->where('redmine_group_id', '=', $this->getAttribute('redmine_group_id'))
            ->where('redmine_user_id', '=', $this->getAttribute('redmine_user_id'));
    }
}
