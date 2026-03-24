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
        Schema::table('personalize_requests', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->string('nin')->nullable();
            $table->text('comments')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personalize_requests', function (Blueprint $table) {
            $table->dropColumn(['name', 'nin', 'comments']);
        });
    }
};
