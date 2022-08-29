<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Inventory\Models\ExistenceType;

class TenantExistenceTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('existence_types', function (Blueprint $table)
        {
            $table->char('id', 2)->primary();
            $table->string('name', 50);
        });

        ExistenceType::query()->insert([
            ['id' => '01', 'name' => 'Mercaderías'],
            ['id' => '02', 'name' => 'Productos Terminados'],
            ['id' => '03', 'name' => 'Materias Primas'],
            ['id' => '04', 'name' => 'Envases'],
            ['id' => '05', 'name' => 'Materiales Auxiliares'],
            ['id' => '06', 'name' => 'Suministros'],
            ['id' => '07', 'name' => 'Repuestos'],
            ['id' => '08', 'name' => 'Embalajes'],
            ['id' => '09', 'name' => 'Subproductos'],
            ['id' => '10', 'name' => 'Desechos y Desperdicios'],
            ['id' => '91', 'name' => 'Otros 1'],
            ['id' => '92', 'name' => 'Otros 2'],
            ['id' => '93', 'name' => 'Otros 3'],
            ['id' => '94', 'name' => 'Otros 4'],
            ['id' => '95', 'name' => 'Otros 5'],
            ['id' => '96', 'name' => 'Otros 6'],
            ['id' => '97', 'name' => 'Otros 7'],
            ['id' => '98', 'name' => 'Otros 8'],
            ['id' => '99', 'name' => 'Otros'],
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('existence_types');
    }

}
