<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_registrations', function (Blueprint $table) {
            $table->id();

            $table->string('surname');
            $table->string('first_name');
            $table->string('other_name')->nullable();
            $table->date('date_of_birth');
            $table->string('gender');
            $table->string('phone_number');

            $table->string('res_state');
            $table->string('res_lga');
            $table->string('res_city');
            $table->string('res_house_number');
            $table->string('res_street_name');
            $table->text('res_description');

            $table->string('bus_state');
            $table->string('bus_lga');
            $table->string('bus_city');
            $table->string('bus_house_number');
            $table->string('bus_street_name');
            $table->text('bus_description');

            $table->string('nature_of_business');
            $table->string('business_name_1');
            $table->string('business_name_2');
            $table->string('email');

            $table->enum('status', ['pending', 'processing', 'failed', 'completed'])->default('pending');

            $table->text('nin_path')->nullable();
            $table->text('signature_path')->nullable();
            $table->text('passport_path')->nullable();

            $table->string('tnx_id');
            $table->string('refno');
            $table->text('response')->nullable();
            $table->text('response_documents')->nullable();
            $table->timestamp('refunded_at')->nullable();

            $table->foreignId('user_id');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_registrations');
    }
};
