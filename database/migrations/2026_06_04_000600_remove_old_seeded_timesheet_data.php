<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('timesheets')
            ->whereNull('desktop_uuid')
            ->where('source', 'manual')
            ->whereDate('date', '<=', '2022-12-31')
            ->update(['deleted_at' => now(), 'updated_at' => now()]);
    }

    public function down(): void
    {
        //
    }
};
