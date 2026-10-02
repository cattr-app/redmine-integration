<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRedminePrioritiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redmine_priorities', function (Blueprint $table) {
            $table->id();
            $table->integer('redmine_priority_id');
            $table->string('name', 256);
            $table->unsignedBigInteger('redmine_instance_id');
            $table->unsignedInteger('priority_id');
            $table->timestamps();

            $table->foreign('redmine_instance_id')->references('id')->on('redmine_instances')->cascadeOnDelete();
            $table->foreign('priority_id')->references('id')->on('priorities')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('redmine_priorities');
    }
}
