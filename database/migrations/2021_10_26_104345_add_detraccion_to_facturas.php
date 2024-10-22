<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDetraccionToFacturas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->boolean('detraccion')->before('por_consumo')->nullable();
            $table->integer('dias_credito')->before('por_consumo')->nullable();
            $table->string('forma_pago', 30)->before('por_consumo')->nullable();
            $table->string('cod_bien_detraccion', 30)->before('por_consumo')->nullable();
            $table->decimal('monto_detraccion', 10,2)->before('por_consumo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('facturas', function (Blueprint $table) {
            //
        });
    }
}
