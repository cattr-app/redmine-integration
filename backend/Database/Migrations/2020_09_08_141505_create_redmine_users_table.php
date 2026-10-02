<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRedmineUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redmine_users', function (Blueprint $table) {
            $table->id();
            $table->integer('redmine_user_id');
            $table->unsignedBigInteger('redmine_instance_id');
            $table->string('api_key', 256);
            $table->unsignedInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('redmine_instance_id')->references('id')->on('redmine_instances')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('redmine_users');
    }
}
