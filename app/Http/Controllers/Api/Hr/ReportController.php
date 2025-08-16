<?php

namespace App\Http\Controllers\Api\Hr;

use App\Http\Controllers\Controller;
use App\Http\Resources\Hr\Report\SalaryLedgerReportResource;
use App\Models\Hr\Employee;

class ReportController extends Controller
{
    public function salaryLedger(Employee $employee)
    {
        $employee->load(['salaryLedgers' => function ($query) {
            $query->with('month');
            $query->where('is_cancelled', 0);
            $query->where('fiscal_year_id', runningFiscalYear()->id);
        }]);

        return new SalaryLedgerReportResource($employee);
    }
}
