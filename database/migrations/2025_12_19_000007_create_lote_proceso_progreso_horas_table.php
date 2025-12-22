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
        Schema::create('lote_proceso_progreso_horas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_proceso_progreso_id')->constrained('lote_proceso_progresos')->onDelete('cascade');
            $table->time('hora');
            $table->integer('piezas_completadas')->default(0);
            $table->integer('piezas_merma')->default(0);
            $table->integer('piezas_excedente')->default(0);
            $table->foreignId('registrado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lote_proceso_progreso_horas');
    }
};
