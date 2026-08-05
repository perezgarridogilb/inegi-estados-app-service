<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('states', function (Blueprint $table) {
            $table->id()
                ->comment('ID autoincremental primario');

            $table->string('cvegeo', 5)
                ->comment('Clave geoestadística asignada por INEGI (ej: 01)');

            $table->string('cve_ent', 5)->unique()
                ->comment('Clave única de la entidad federativa (ej: 01, 09)');

            $table->string('nomgeo', 100)
                ->comment('Nombre oficial de la entidad federativa');

            $table->string('nom_abrev', 20)->nullable()
                ->comment('Abreviatura oficial de la entidad (ej: Ags., CDMX)');

            $table->unsignedBigInteger('pob_total')->default(0)
                ->comment('Población total registrada en el Censo INEGI');

            $table->unsignedBigInteger('pob_femenina')->default(0)
                ->comment('Población total de mujeres');

            $table->unsignedBigInteger('pob_masculina')->default(0)
                ->comment('Población total de hombres');

            $table->unsignedBigInteger('total_viviendas_habitadas')->default(0)
                ->comment('Total de viviendas particulares habitadas');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('States');
    }
};
