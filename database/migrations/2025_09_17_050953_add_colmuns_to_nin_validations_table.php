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
        Schema::table('nin_validations', function (Blueprint $table) {
            $table->string('tag')->nullable();
            $table->string('tracking_no')->nullable();
            $table->enum('resp_code', ['100', '101', '200', '400'])
                ->after('description')
                ->default('100');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nin_validations', function (Blueprint $table) {
            $table->dropColumn(['tag', 'tracking_no', 'resp_code']);
        });
    }
};
