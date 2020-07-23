<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRedmineInstances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redmine_instances', function (Blueprint $table) {
            $table->id();
            $table->string('url', 256);
            $table->string('api_key', 256);
            $table->string('sync_type', 32);

            $table->boolean('sync_new_projects');

            $table->integer('project_sync_interval');
            $table->integer('task_sync_interval');
            $table->integer('priority_sync_interval');
            $table->integer('status_sync_interval');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('redmine_instances');
    }
}
