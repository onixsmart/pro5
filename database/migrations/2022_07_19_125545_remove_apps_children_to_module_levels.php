<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveAppsChildrenToModuleLevels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('module_levels', function (Blueprint $table) {
            \App\Models\System\ModuleLevel::query()->where('module_id',15)->delete();
            \App\Models\System\ModuleLevel::query()->where('module_id',16)->delete();
            \App\Models\System\ModuleLevel::query()->where('module_id',19)->delete();
            \App\Models\System\ModuleLevel::query()->where('module_id',21)->delete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('module_levels', function (Blueprint $table) {
            //
        });
    }
}
