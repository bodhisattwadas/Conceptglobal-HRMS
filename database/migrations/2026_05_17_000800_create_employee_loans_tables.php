<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_loans', function (Blueprint $table): void {
            $table->id();
            $table->string('loan_number', 40)->unique();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_position_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->date('request_date');
            $table->decimal('loan_amount', 15, 2)->default(0);
            $table->unsignedInteger('number_of_installments')->default(1);
            $table->date('payment_start_date')->nullable();
            $table->string('currency_code', 10)->default('USD');
            $table->string('treasury_account')->nullable();
            $table->string('loan_account')->nullable();
            $table->string('journal')->nullable();
            $table->string('status', 20)->default('draft');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('total_paid_amount', 15, 2)->default(0);
            $table->decimal('balance_amount', 15, 2)->default(0);
            $table->text('reason')->nullable();
            $table->text('refusal_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['employee_id', 'status']);
        });

        Schema::create('employee_loan_installments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_loan_id')->constrained('employee_loans')->cascadeOnDelete();
            $table->unsignedInteger('installment_no');
            $table->date('payment_date');
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);
            $table->string('status', 20)->default('pending');
            $table->date('paid_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_loan_installments');
        Schema::dropIfExists('employee_loans');
    }
};
