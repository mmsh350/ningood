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
        Schema::table('nin_modifications', function (Blueprint $blueprint) {
            $blueprint->string('education_qualification')->nullable();
            $blueprint->string('marital_status')->nullable();
            $blueprint->string('father_full_name')->nullable();
            $blueprint->string('father_state_of_origin')->nullable();
            $blueprint->string('father_lga_of_origin')->nullable();
            $blueprint->string('mother_full_name')->nullable();
            $blueprint->string('mother_state_of_origin')->nullable();
            $blueprint->string('mother_lga_of_origin')->nullable();
            $blueprint->string('mother_maiden_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nin_modifications', function (Blueprint $blueprint) {
            $blueprint->dropColumn([
                'education_qualification',
                'marital_status',
                'father_full_name',
                'father_state_of_origin',
                'father_lga_of_origin',
                'mother_full_name',
                'mother_state_of_origin',
                'mother_lga_of_origin',
                'mother_maiden_name',
            ]);
        });
    }
};
