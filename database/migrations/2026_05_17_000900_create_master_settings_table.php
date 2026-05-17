<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('default_currency_code', 10)->default('INR');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_settings');
    }
};
