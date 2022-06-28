<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddColumnsToOrderNotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_notes', function (Blueprint $table) {
            $table->string('series', 4)->nullable()->after('prefix');
            $table->integer('number')->nullable()->after('series');
            $table->unsignedInteger('customer_address_id')->nullable()->after('customer');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_notes', function (Blueprint $table) {
            $table->dropColumn('series');
            $table->dropColumn('number');
            $table->dropColumn('customer_address_id');
        });
    }
}
