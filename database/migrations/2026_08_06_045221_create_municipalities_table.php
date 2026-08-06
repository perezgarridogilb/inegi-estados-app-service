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
        Schema::create('municipalities', function (Blueprint $table) {
            $table->id()
                ->comment('Identificador único autoincrementable del registro local');

            $table->string('cve_ent', 5)
                ->comment('Clave de la entidad federativa (cve_ent del INEGI, ej. "01")');

            $table->string('cve_mun', 5)
                ->comment('Clave del municipio (cve_mun del INEGI, ej. "001")');

            $table->string('nomgeo', 150)
                ->comment('Nombre geográfico del municipio (nomgeo del INEGI)');

            $table->unsignedBigInteger('pob_total')
                ->default(0)
                ->comment('Población total registrada en el municipio');

            $table->timestamps();

            // Llave foránea que referencia a la clave del estado (cve_ent)
            $table->foreign('cve_ent')
                ->references('cve_ent')
                ->on('states')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // Índice único compuesto para garantizar idempotencia y evitar duplicados por estado/municipio
            $table->unique(['cve_ent', 'cve_mun'], 'uk_estado_municipio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('municipalities');
    }
};
