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
        Schema::create('proceso_nodo_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_nodo_id')->constrained('proceso_nodos')->onDelete('cascade');
            $table->foreignId('padre_proceso_nodo_id')->constrained('proceso_nodos')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['proceso_nodo_id', 'padre_proceso_nodo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proceso_nodo_dependencies');
    }
};
