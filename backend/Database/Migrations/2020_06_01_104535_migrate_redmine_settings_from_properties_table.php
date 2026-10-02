<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class MigrateRedmineSettingsFromPropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $companySettings = DB::table('properties')
            ->where('entity_type', 'company')
            ->whereRaw('LOWER(name) like ?', 'redmine_%')
            ->get();

        foreach ($companySettings as $property) {
            DB::table('settings')->insert([
                'module_name' => 'redmine',
                'key' => strtolower(substr(strstr($property->name, '_'), 1)),
                'value' => $property->value,
            ]);

            DB::table('properties')->delete($property->id);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $redmineSettings = DB::table('settings')
            ->where('module_name', 'redmine')
            ->get();

        foreach ($redmineSettings as $setting) {
            DB::table('properties')->insert([
                'entity_id' => 0,
                'entity_type' => 'company',
                'name' => "redmine_{$setting->key}",
                'value' => $setting->value,
            ]);

            DB::table('settings')->delete($setting->id);
        }
    }
}
