<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_contribution_registers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('payroll_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('default_currency_code', 10)->default('INR');
            $table->boolean('payroll_approval_required')->default(true);
            $table->boolean('include_attendance_in_payroll')->default(true);
            $table->boolean('include_leave_in_payroll')->default(true);
            $table->boolean('include_timesheet_in_payroll')->default(false);
            $table->decimal('default_working_days_per_month', 8, 2)->default(26);
            $table->decimal('default_working_hours_per_day', 8, 2)->default(8);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_settings');
        Schema::dropIfExists('payroll_contribution_registers');
    }
};
