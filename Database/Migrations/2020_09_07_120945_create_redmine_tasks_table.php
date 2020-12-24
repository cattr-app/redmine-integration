<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRedmineTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redmine_tasks', function (Blueprint $table) {
            $table->id();
            $table->integer('redmine_issue_id');
            $table->unsignedBigInteger('redmine_instance_id');
            $table->unsignedInteger('task_id')->nullable();
            $table->timestamps();

            $table->foreign('redmine_instance_id')->references('id')->on('redmine_instances')->cascadeOnDelete();
            $table->foreign('task_id')->references('id')->on('tasks')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('redmine_tasks');
    }
}
