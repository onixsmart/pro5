<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddOrderNoteIdToKardex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kardex', function (Blueprint $table)
        {
            $table->unsignedInteger('order_note_id')->nullable()->after('sale_note_id');
            $table->foreign('order_note_id')->references('id')->on('order_notes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kardex', function (Blueprint $table)
        {
            $table->dropForeign(['order_note_id']);
            $table->dropColumn('order_note_id');
        });
    }
}
