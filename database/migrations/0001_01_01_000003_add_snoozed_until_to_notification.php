<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE lbaw2544.notification ADD COLUMN snoozed_until TIMESTAMP NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE lbaw2544.notification DROP COLUMN snoozed_until");
    }
};
