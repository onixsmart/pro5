<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddAssignPercentageToItemUnitTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_unit_types', function (Blueprint $table) {
            $table->boolean('assing_percentage')->after('assing_price')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_unit_types', function (Blueprint $table) {
            $table->dropColumn('assing_percentage');
        });
    }
}
