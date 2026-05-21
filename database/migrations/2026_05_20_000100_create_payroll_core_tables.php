<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_contracts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_position_id')->nullable()->constrained()->nullOnDelete();
            $table->string('contract_name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('notice_period_days')->default(0);
            $table->string('employee_category')->nullable();
            $table->string('salary_structure')->nullable();
            $table->string('salary_structure_type')->nullable();
            $table->string('working_schedule')->nullable();
            $table->string('hr_responsible')->nullable();
            $table->string('state')->default('new');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('payroll_salary_structures', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('reference');
            $table->integer('salary_rules_count')->default(0);
            $table->timestamps();
        });

        Schema::create('payroll_salary_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->integer('sequence')->default(10);
            $table->boolean('active')->default(true);
            $table->boolean('appears_on_payslip')->default(true);
            $table->string('condition_based_on')->nullable();
            $table->string('amount_type')->nullable();
            $table->text('python_code')->nullable();
            $table->string('contribution_register')->nullable();
            $table->timestamps();
        });

        Schema::create('payroll_payslip_batches', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->boolean('credit_note')->default(false);
            $table->string('state')->default('draft');
            $table->timestamps();
        });

        Schema::create('payroll_payslips', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payroll_payslip_batch_id')->nullable()->constrained('payroll_payslip_batches')->nullOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('reference');
            $table->string('name');
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_payslips');
        Schema::dropIfExists('payroll_payslip_batches');
        Schema::dropIfExists('payroll_salary_rules');
        Schema::dropIfExists('payroll_salary_structures');
        Schema::dropIfExists('payroll_contracts');
    }
};
