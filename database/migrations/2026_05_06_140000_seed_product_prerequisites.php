<?php

use Database\Seeders\ProductPrerequisitesSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        (new ProductPrerequisitesSeeder())->run();
    }

    public function down(): void
    {
        // Seed-only migration; no destructive rollback.
    }
};
