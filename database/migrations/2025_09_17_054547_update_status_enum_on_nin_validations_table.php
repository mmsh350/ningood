<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE nin_validations MODIFY status ENUM('Successful', 'Pending', 'In-Progress', 'Failed') DEFAULT 'Pending'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE nin_validations MODIFY status ENUM('resolved', 'pending', 'rejected', 'processing') DEFAULT 'pending'");
    }
};
