<?php

Route::middleware('auth:api')->group(static function () {
    // Task routes
    Route::post('/tasks/synchronize', 'TaskRedmineController@synchronize')->name('task.synchronize');

    // Project routes
    Route::post('/projects/synchronize', 'ProjectRedmineController@synchronize')
        ->name('projects.synchronize');

    // Time Entry routes
    Route::put('/time-entries', 'TimeEntryRedmineController@create')->name('time-entries.put');

    // Redmine Settings routes
    Route::get('/settings', 'SettingController@index')->name('settings.index');
    Route::patch('/settings', 'SettingController@update')->name('settings.update');

    // User settings routes
    Route::get('/user-settings', 'UserSettingController@index')->name('user-settings.index');
    Route::patch('/user-settings', 'UserSettingController@update')->name('user-settings.update');

    Route::get('/settings/priorities', 'PriorityController@index')->name('settings.priorities');
});

Route::group([
    'middleware' => 'redmineintegration.signature',
    'as' => 'plugin.',
    'prefix' => 'plugin'
], static function () {
    Route::post('update', 'TaskUpdateController@handleUpdate')->name('update');
});
