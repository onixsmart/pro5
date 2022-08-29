<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddCostControlToInventoryConfigurations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_configurations', function (Blueprint $table) {
            $table->boolean('cost_control')->default(false)->after('generate_internal_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_configurations', function (Blueprint $table) {
            $table->dropColumn('cost_control');
        });
    }
}
