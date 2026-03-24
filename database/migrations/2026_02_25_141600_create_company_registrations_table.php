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
        Schema::create('company_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');

            // Director Details
            $table->string('director_surname');
            $table->string('director_firstname');
            $table->string('director_othername')->nullable();
            $table->date('director_dob');
            $table->string('director_gender');
            $table->string('director_email');
            $table->string('director_phone');
            $table->string('director_nin');

            // Residential Address
            $table->string('res_state');
            $table->string('res_lga');
            $table->string('res_city');
            $table->string('res_house_number');
            $table->string('res_street_name');
            $table->text('res_description');

            // Business Address
            $table->string('bus_state');
            $table->string('bus_lga');
            $table->string('bus_city');
            $table->string('bus_house_number');
            $table->string('bus_street_name');
            $table->text('bus_description');

            // Business Details
            $table->text('nature_of_business');
            $table->string('business_name_1');
            $table->string('business_name_2');
            $table->string('business_email');

            // Witness Info
            $table->string('witness_surname');
            $table->string('witness_firstname');
            $table->string('witness_othername')->nullable();
            $table->string('witness_phone');
            $table->string('witness_email');
            $table->string('witness_nin');
            $table->text('witness_address');

            // Shareholder Details
            $table->string('shareholder_surname');
            $table->string('shareholder_firstname');
            $table->string('shareholder_othername')->nullable();
            $table->date('shareholder_dob');
            $table->string('shareholder_gender');
            $table->string('shareholder_nationality')->default('Nigerian');
            $table->string('shareholder_phone');
            $table->string('shareholder_email');
            $table->string('shareholder_nin');
            $table->text('shareholder_address');

            // Document Paths
            $table->string('director_signature_path');
            $table->string('witness_signature_path');
            $table->string('shareholder_signature_path');

            // Transaction & Metadata
            $table->string('refno')->unique();
            $table->unsignedBigInteger('tnx_id');
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->text('admin_comment')->nullable();
            $table->json('response_documents')->nullable();
            $table->dateTime('refunded_at')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_registrations');
    }
};
