<?php

namespace Modules\RedmineIntegration\Models;

use App\Models\Setting as SettingModel;

class Setting extends SettingModel
{
    protected $casts = [
        'value' => 'string',
        'sync' => 'bool',
        'enabled' => 'bool',
        'statuses' => 'json',
        'priorities' => 'json'
    ];
}
