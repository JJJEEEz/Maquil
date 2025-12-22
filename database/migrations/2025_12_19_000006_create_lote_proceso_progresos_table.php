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
        Schema::create('lote_proceso_progresos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_id')->constrained('lotes')->onDelete('cascade');
            $table->foreignId('proceso_nodo_id')->constrained('proceso_nodos')->onDelete('cascade');
            $table->integer('cantidad_objetivo')->default(0);
            $table->integer('cantidad_completada')->default(0);
            $table->integer('cantidad_merma')->default(0);
            $table->integer('cantidad_excedente')->default(0);
            $table->enum('estado', ['pendiente', 'en_progreso', 'completado'])->default('pendiente');
            $table->foreignId('registrado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('editado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('inicio_proceso')->nullable();
            $table->timestamp('fin_proceso')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->unique(['lote_id', 'proceso_nodo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lote_proceso_progresos');
    }
};
