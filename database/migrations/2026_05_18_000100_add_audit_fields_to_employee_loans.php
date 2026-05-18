<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_loans', function (Blueprint $table): void {
            $table->text('notes')->nullable()->after('reason');
            $table->timestamp('submitted_at')->nullable()->after('notes');
            $table->string('submitted_by', 120)->nullable()->after('submitted_at');
            $table->string('submitted_ip', 64)->nullable()->after('submitted_by');
            $table->timestamp('approved_at')->nullable()->after('submitted_ip');
            $table->string('approved_by', 120)->nullable()->after('approved_at');
            $table->string('approved_ip', 64)->nullable()->after('approved_by');
            $table->timestamp('refused_at')->nullable()->after('approved_ip');
            $table->string('refused_by', 120)->nullable()->after('refused_at');
            $table->string('refused_ip', 64)->nullable()->after('refused_by');
            $table->timestamp('cancelled_at')->nullable()->after('refused_ip');
            $table->string('cancelled_by', 120)->nullable()->after('cancelled_at');
            $table->string('cancelled_ip', 64)->nullable()->after('cancelled_by');
        });
    }

    public function down(): void
    {
        Schema::table('employee_loans', function (Blueprint $table): void {
            $table->dropColumn([
                'notes',
                'submitted_at', 'submitted_by', 'submitted_ip',
                'approved_at', 'approved_by', 'approved_ip',
                'refused_at', 'refused_by', 'refused_ip',
                'cancelled_at', 'cancelled_by', 'cancelled_ip',
            ]);
        });
    }
};
