<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRedmineGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redmine_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('redmine_instance_id');
            $table->unsignedBigInteger('redmine_group_id');
            $table->timestamps();

            $table->foreign('redmine_instance_id')->references('id')->on('redmine_instances')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('redmine_groups');
    }
}
