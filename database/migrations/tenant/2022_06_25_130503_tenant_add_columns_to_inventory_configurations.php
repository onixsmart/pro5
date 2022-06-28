<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddColumnsToInventoryConfigurations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_configurations', function (Blueprint $table) {
            $table->boolean('stock_change_by_order_notes')->default(false)->after('generate_internal_id');
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
            $table->dropColumn('stock_change_by_order_notes');
        });
    }
}
