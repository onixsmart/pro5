<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreditInformationsToDocumentItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('document_items', function (Blueprint $table) {
            $table->decimal('credit_cap', 12, 2)->nullable();
            $table->decimal('credit_int', 12, 2)->nullable();
            $table->decimal('credit_mor', 12, 2)->nullable();
            $table->integer('credit_fee')->nullable();
            $table->integer('credit_amorti')->nullable();
            $table->integer('credit_pend')->nullable();
            $table->decimal('credit_tot', 12, 2)->nullable();
            $table->date('credit_dat')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('document_items', function (Blueprint $table) {
            $table->dropColumn('credit_cap');
            $table->dropColumn('credit_int');
            $table->dropColumn('credit_mor');
            $table->dropColumn('credit_fee');
            $table->dropColumn('credit_amorti');
            $table->dropColumn('credit_pend');
            $table->dropColumn('credit_tot');
            $table->dropColumn('credit_dat');
        });
    }
}
