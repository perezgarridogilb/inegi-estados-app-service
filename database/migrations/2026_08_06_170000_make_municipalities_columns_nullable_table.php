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
        Schema::table('municipalities', function (Blueprint $table) {
            $table->string('cve_mun', 5)->nullable()->change();
            $table->string('nomgeo', 150)->nullable()->change();
            $table->unsignedBigInteger('pob_total')->default(0)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('municipalities', function (Blueprint $table) {
            $table->string('cve_mun', 5)->nullable(false)->change();
            $table->string('nomgeo', 150)->nullable(false)->change();
            $table->unsignedBigInteger('pob_total')->default(0)->nullable(false)->change();
        });
    }
};
