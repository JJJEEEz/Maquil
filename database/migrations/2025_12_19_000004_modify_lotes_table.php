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
        // Primero, verificamos si la tabla existe y la recreamos con la nueva estructura
        Schema::table('lotes', function (Blueprint $table) {
            // Agregar nuevas columnas
            $table->date('fecha')->nullable()->after('orden_id');
            $table->enum('estado_trabajo', ['trabajado', 'no_trabajado', 'interrumpido'])->default('trabajado')->after('quantity');
            $table->string('razon_interrupcion')->nullable()->after('estado_trabajo');
            $table->integer('total_prendas_terminadas')->default(0)->after('razon_interrupcion');
            $table->integer('total_mermas')->default(0)->after('total_prendas_terminadas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lotes', function (Blueprint $table) {
            $table->dropColumn(['fecha', 'estado_trabajo', 'razon_interrupcion', 'total_prendas_terminadas', 'total_mermas']);
        });
    }
};
