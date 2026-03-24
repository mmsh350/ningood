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
        Schema::table('business_registrations', function (Blueprint $table) {
            $table->enum('status', ['pending', 'processing', 'failed', 'completed', 'query'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_registrations', function (Blueprint $table) {
            $table->enum('status', ['pending', 'processing', 'failed', 'completed'])->default('pending')->change();
        });
    }
};
