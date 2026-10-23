<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddManualUnlinkedToRedmineProjects extends Migration
{
    public function up(): void
    {
        Schema::table('redmine_projects', function (Blueprint $table): void {
            // Legacy NULL mappings are linkable by cron, but kept unlinked in webhook mode.
            // Explicit admin unlinks use true and are never auto-linked.
            $table->boolean('manual_unlinked')->nullable()->default(null);
        });
    }

    public function down(): void
    {
        Schema::table('redmine_projects', function (Blueprint $table): void {
            $table->dropColumn('manual_unlinked');
        });
    }
}
