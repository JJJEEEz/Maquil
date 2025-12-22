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
        Schema::create('proceso_nodos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_prenda_id')->constrained('tipos_prendas')->onDelete('cascade');
            $table->string('nombre');
            $table->enum('tipo', ['operacion', 'inspeccion'])->default('operacion');
            $table->integer('orden')->default(0);
            $table->integer('cantidad_entrada')->default(1);
            $table->integer('cantidad_salida')->default(1);
            $table->foreignId('parent_id')->nullable()->constrained('proceso_nodos')->onDelete('cascade');
            $table->integer('tiempo_estimado_minutos')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proceso_nodos');
    }
};
