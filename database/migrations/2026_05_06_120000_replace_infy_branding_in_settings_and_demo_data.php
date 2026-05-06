<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settingKeys = ['footer', 'developed', 'company_name', 'email', 'logo'];

        $rows = DB::table('settings')->whereIn('key', $settingKeys)->get();

        foreach ($rows as $row) {
            $value = $row->value;
            if ($value === null || $value === '' || ! preg_match('/infy/i', (string) $value)) {
                continue;
            }

            $replacement = match ($row->key) {
                'company_name' => 'Smart Shopping System',
                'footer' => 'All rights reserved',
                'developed' => 'Smart Shopping System',
                'email' => 'support@example.com',
                'logo' => 'images/brand_logo.png',
                default => (string) $value,
            };

            DB::table('settings')->where('id', $row->id)->update([
                'value' => is_string($replacement) ? trim($replacement) : '',
                'updated_at' => now(),
            ]);
        }

        DB::table('customers')
            ->where('email', 'customer@infypos.com')
            ->update(['email' => 'walk-in@customer.local', 'updated_at' => now()]);

        DB::table('warehouses')
            ->where('email', 'warehouse1@infypos.com')
            ->update(['email' => 'warehouse@warehouse.local', 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Irreversible data cleanup — no rollback of branding text.
    }
};
