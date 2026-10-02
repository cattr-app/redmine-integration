<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRedmineGroupsUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redmine_groups_users', function (Blueprint $table) {
            $table->unsignedBigInteger('redmine_group_id');
            $table->unsignedBigInteger('redmine_user_id');
            $table->timestamps();

            $table->foreign('redmine_group_id')->references('id')->on('redmine_groups');
            $table->foreign('redmine_user_id')->references('id')->on('redmine_users');

            $table->primary(['redmine_group_id', 'redmine_user_id'], 'redmine_groups_users_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('redmine_groups_users');
    }
}
