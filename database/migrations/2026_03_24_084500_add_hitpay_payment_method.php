<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('payment_methods')->updateOrInsert(
            ['id' => 5],
            [
                'name' => 'HitPay',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('payment_methods')->where('id', 5)->delete();
    }
};
