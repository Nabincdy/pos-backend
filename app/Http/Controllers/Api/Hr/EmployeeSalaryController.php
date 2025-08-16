<?php

namespace App\Http\Controllers\Api\Hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Hr\EmployeeSalary\StoreEmployeeSalaryRequest;
use App\Http\Requests\Api\Hr\EmployeeSalary\UpdateEmployeeSalaryRequest;
use App\Http\Resources\Hr\EmployeeSalaryResource;
use App\Http\Resources\Hr\SalaryListResource;
use App\Models\Hr\Employee;
use App\Models\Hr\EmployeeSalary;
use Illuminate\Support\Facades\DB;

class EmployeeSalaryController extends Controller
{
    public function salaryList()
    {
        $this->checkAuthorization('employeeSalary_access');

        $employees = Employee::with('latestSalary.salaryStructures.payHead')->whereHas('latestSalary')->orderBy('rank')->get();

        return SalaryListResource::collection($employees);
    }

    public function index(Employee $employee)
    {
        $this->checkAuthorization('employeeSalary_access');

        $employee->load('employeeSalaries.salaryStructures.payHead');

        return EmployeeSalaryResource::collection($employee->employeeSalaries);
    }

    public function store(StoreEmployeeSalaryRequest $request, Employee $employee)
    {
        $this->checkAuthorization('employeeSalary_access');

        $employeeSalary = DB::transaction(function () use ($request, $employee) {
            $employeeSalary = $employee->employeeSalaries()->create($request->validated());

            foreach ($request->validated()['salaryStructures'] as $salaryStructure) {
                $employeeSalary->salaryStructures()->create($salaryStructure);
            }

            return $employeeSalary;
        });

        return response()->json([
            'data' => new EmployeeSalaryResource($employeeSalary->load('salaryStructures.payHead')),
            'message' => 'Employee Salary Added Successfully',
        ], 201);
    }

    public function show(Employee $employee, EmployeeSalary $employeeSalary)
    {
        $this->checkAuthorization('employeeSalary_access');

        return new EmployeeSalaryResource($employeeSalary->load('salaryStructures'));
    }

    public function update(UpdateEmployeeSalaryRequest $request, Employee $employee, EmployeeSalary $employeeSalary)
    {
        $this->checkAuthorization('employeeSalary_edit');
    }

    public function destroy(Employee $employee, EmployeeSalary $employeeSalary)
    {
        $this->checkAuthorization('employeeSalary_delete');

        $employeeSalary->salaryStructures()->delete();
        $employeeSalary->delete();

        return response()->json([
            'data' => '',
            'message' => 'Employee Salary Deleted Successfully',
        ]);
    }
}
