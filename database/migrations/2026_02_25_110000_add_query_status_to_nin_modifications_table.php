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
        Schema::table('nin_modifications', function (Blueprint $table) {
            $table->enum('status', ['resolved', 'pending', 'rejected', 'processing', 'query'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nin_modifications', function (Blueprint $table) {
            $table->enum('status', ['resolved', 'pending', 'rejected', 'processing'])->default('pending')->change();
        });
    }
};
