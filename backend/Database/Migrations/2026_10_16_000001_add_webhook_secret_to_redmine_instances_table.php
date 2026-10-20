<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWebhookSecretToRedmineInstancesTable extends Migration
{
    public function up(): void
    {
        Schema::table('redmine_instances', static function (Blueprint $table) {
            $table->text('webhook_secret')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('redmine_instances', static function (Blueprint $table) {
            $table->dropColumn('webhook_secret');
        });
    }
}
