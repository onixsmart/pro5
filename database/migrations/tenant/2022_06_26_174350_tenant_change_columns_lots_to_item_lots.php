<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantChangeColumnsLotsToItemLots extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_lots', function (Blueprint $table)
        {
            $table->unsignedInteger('item_loteable_id')->nullable()->change();
            $table->string('item_loteable_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_lots', function (Blueprint $table)
        {

        });
    }
}
