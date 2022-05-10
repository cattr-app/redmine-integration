<?php

namespace Modules\RedmineIntegration\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modules\RedmineIntegration\Entities\RedmineUser
 *
 * @property int $id
 * @property int $redmine_user_id
 * @property int $redmine_instance_id
 * @property string $api_key
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\RedmineIntegration\Entities\RedmineGroup[] $groups
 * @property-read int|null $groups_count
 * @property-read \Modules\RedmineIntegration\Entities\RedmineInstance $redmineInstance
 * @property-read User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineUser whereApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineUser whereRedmineInstanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineUser whereRedmineUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedmineUser whereUserId($value)
 * @mixin \Eloquent
 */
class RedmineUser extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'redmine_user_id',
        'redmine_instance_id',
        'api_key',
        'user_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'redmine_user_id' => 'int',
        'redmine_instance_id' => 'int',
        'api_key' => 'string',
        'user_id' => 'int',
    ];

    /**
     * @var array
     */
    protected $hidden = [
        'api_key',
    ];

    public function redmineInstance(): BelongsTo
    {
        return $this->belongsTo(RedmineInstance::class, 'redmine_instance_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function groups()
    {
        return $this->belongsToMany(RedmineGroup::class, 'redmine_groups_users');
    }
}
