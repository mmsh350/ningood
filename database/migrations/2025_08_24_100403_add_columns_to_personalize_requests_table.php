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
            $table->enum('resp_code', ['100', '101', '200', '400'])
                ->after('status')
                ->default('100');
            $table->string('tag')->after('resp_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personalize_requests', function (Blueprint $table) {
            $table->dropColumn('resp_code', 'tag');
        });
    }
};
