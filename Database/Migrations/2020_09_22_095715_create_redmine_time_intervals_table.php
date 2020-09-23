<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRedmineTimeIntervalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redmine_time_intervals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('redmine_instance_id');
            $table->unsignedInteger('time_interval_id');
            $table->timestamps();

            $table->foreign('redmine_instance_id')->references('id')->on('redmine_instances')->cascadeOnDelete();
            $table->foreign('time_interval_id')->references('id')->on('time_intervals')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('redmine_time_intervals');
    }
}
