<?php

Route::middleware('auth:api')->group(static function () {
    // Redmine instances
    Route::get('/instances', 'RedmineInstanceController@list')->name('redmine-instances.list');
    Route::get('/instances/{id}', 'RedmineInstanceController@show')->name('redmine-instances.show');
    Route::post('/instances', 'RedmineInstanceController@create')->name('redmine-instances.create');
    Route::put('/instances/{id}', 'RedmineInstanceController@update')->name('redmine-instances.update');
    Route::delete('/instances/{id}', 'RedmineInstanceController@delete')->name('redmine-instances.delete');

    // Redmine priorities
    Route::get('/instances/{instanceId}/priorities', 'RedminePriorityController@list')->name('redmine-priorities.list');
    Route::put('/instances/{instanceId}/priorities', 'RedminePriorityController@bulkUpdate')->name('redmine-priorities.bulk-update');
    Route::get('/instances/{instanceId}/priorities/{id}', 'RedminePriorityController@show')->name('redmine-priorities.show');
    Route::put('/instances/{instanceId}/priorities/{id}', 'RedminePriorityController@update')->name('redmine-priorities.update');

    // Redmine statuses
    Route::get('/instances/{instanceId}/statuses', 'RedmineStatusController@list')->name('redmine-statuses.list');
    Route::get('/instances/{instanceId}/statuses/{id}', 'RedmineStatusController@show')->name('redmine-statuses.show');
    Route::put('/instances/{instanceId}/statuses/{id}', 'RedmineStatusController@update')->name('redmine-statuses.update');

    // Redmine projects
    Route::get('/instances/{instanceId}/projects', 'RedmineProjectController@list')->name('redmine-projects.list');
    Route::get('/instances/{instanceId}/projects/{id}', 'RedmineProjectController@show')->name('redmine-projects.show');
    Route::put('/instances/{instanceId}/projects/{id}', 'RedmineProjectController@update')->name('redmine-projects.update');

    // Redmine users
    Route::post('/instances/{instanceId}/users', 'RedmineUserController@create')->name('redmine-users.create');
});
