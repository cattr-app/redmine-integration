<?php

namespace Modules\RedmineIntegration\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
