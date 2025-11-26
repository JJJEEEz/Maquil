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
        Schema::table('ordenes', function (Blueprint $table) {
            $table->string('client')->nullable()->after('description');
            $table->string('quality')->nullable()->after('client');
            $table->string('status')->default('pending')->after('quality');
            $table->unsignedInteger('target_quantity')->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->dropColumn(['client', 'quality', 'status', 'target_quantity']);
        });
    }
};
