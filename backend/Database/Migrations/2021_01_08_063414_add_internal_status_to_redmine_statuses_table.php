<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInternalStatusToRedmineStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('redmine_statuses', function (Blueprint $table) {
            $table->unsignedInteger('status_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('redmine_statuses', function (Blueprint $table) {
            $table->dropColumn('status_id');
        });
    }
}
