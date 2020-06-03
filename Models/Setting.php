<?php

namespace Modules\RedmineIntegration\Models;

use App\Models\Setting as SettingModel;

class Setting extends SettingModel
{
    protected $casts = [
        'value' => 'string',
        'url' => 'string',
        'api_key' => 'string',
        'sync' => 'bool',
        'enabled' => 'bool',
        'statuses' => 'json',
        'priorities' => 'json'
    ];
}
