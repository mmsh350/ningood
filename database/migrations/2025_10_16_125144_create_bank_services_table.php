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

        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('bank_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_id');
            $table->foreignId('service_id');
            $table->decimal('price', 10, 2);
            $table->decimal('commission', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['bank_id', 'service_id']);
        });

        Schema::create('modification_requests', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('refno');
            $table->string('bvn_no');
            $table->string('nin_number');
            $table->foreignId('bank_id');
            $table->foreignId('service_id');
            $table->json('modification_data');
            $table->decimal('base_price', 10, 2);
            $table->decimal('commission', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2);
            $table->enum('status', ['pending', 'resolved', 'rejected', 'processing'])->default('pending');
            $table->timestamp('refunded_at')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modification_requests');
        Schema::dropIfExists('bank_services');
        Schema::dropIfExists('banks');
    }
};
