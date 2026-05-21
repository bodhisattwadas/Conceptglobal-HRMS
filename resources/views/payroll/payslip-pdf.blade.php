<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payslip</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        .header { border-bottom: 2px solid #7b5aa6; padding-bottom: 8px; margin-bottom: 16px; }
        .title { font-size: 20px; font-weight: 700; color: #2f3a56; }
        .sub { color: #6b7280; margin-top: 4px; }
        .grid { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .grid td { padding: 8px 6px; vertical-align: top; }
        .label { font-weight: 700; width: 180px; color: #111827; }
        .value { color: #374151; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 10px; background: #e5e7eb; font-size: 11px; }
        .footer { margin-top: 30px; font-size: 11px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Concept Global - Payslip</div>
        <div class="sub">System Generated Salary Slip</div>
    </div>

    <table class="grid">
        <tr>
            <td class="label">Reference</td>
            <td class="value">{{ $payslip->reference }}</td>
            <td class="label">Status</td>
            <td class="value"><span class="badge">{{ strtoupper($payslip->status) }}</span></td>
        </tr>
        <tr>
            <td class="label">Payslip Name</td>
            <td class="value">{{ $payslip->name }}</td>
            <td class="label">Currency</td>
            <td class="value">INR</td>
        </tr>
        <tr>
            <td class="label">Employee</td>
            <td class="value">{{ $payslip->employee?->full_name }}</td>
            <td class="label">Batch</td>
            <td class="value">{{ $payslip->batch?->name }}</td>
        </tr>
        <tr>
            <td class="label">Period</td>
            <td class="value">{{ optional($payslip->date_from)->format('d/m/Y') }} - {{ optional($payslip->date_to)->format('d/m/Y') }}</td>
            <td class="label">Generated At</td>
            <td class="value">{{ now()->format('d/m/Y H:i:s') }}</td>
        </tr>
    </table>

    <div class="footer">
        This is a computer-generated payroll document and does not require signature.
    </div>
</body>
</html>
