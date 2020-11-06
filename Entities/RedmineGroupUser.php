<?php

namespace Modules\RedmineIntegration\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected function setKeysForSaveQuery(Builder $query): Builder
    {
        return $query->where('redmine_group_id', '=', $this->getAttribute('redmine_group_id'))
            ->where('redmine_user_id', '=', $this->getAttribute('redmine_user_id'));
    }
}
